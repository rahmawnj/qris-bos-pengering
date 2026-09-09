<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use App\Models\Owner;
use App\Models\QrisBillingPayment;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class QrisBillingController extends Controller
{
    public function index(Request $request) { $period=$request->input('period'); $ownerId=$request->input('owner_id'); $search=$request->input('search'); $payments=QrisBillingPayment::with(['outlet.owner.user','outlet.owner.withdrawals'])->where('status','pending')->when($period,fn($q)=>$q->whereRaw("DATE_FORMAT(period_end, '%Y-%m') = ?",[$period]))->whereHas('outlet',function($q)use($ownerId,$search){$q->when($ownerId,fn($x)=>$x->where('owner_id',$ownerId))->when($search,function($x)use($search){$x->where(function($i)use($search){$i->where('outlet_name','like',"%{$search}%")->orWhere('code','like',"%{$search}%")->orWhereHas('owner',function($o)use($search){$o->where('brand_name','like',"%{$search}%")->orWhere('name','like',"%{$search}%");});});});})->latest('created_at')->get(); $summary=$this->summaryFromPayments($payments); $owners=Owner::orderBy('brand_name')->get(); return view('admin.qris_billing.index',compact('payments','period','ownerId','search','summary','owners')); }

    public function blocked(Request $request) { $ownerId=$request->input('owner_id'); $search=$request->input('search'); $today=now()->toDateString(); $outlets=$this->baseOutletQuery()->where(function($q)use($today){$q->whereNull('qris_billing_due_date')->orWhereDate('qris_billing_due_date','<',$today);})->whereDoesntHave('qrisBillingPayments',fn($q)=>$q->where('status','pending'))->when($ownerId,fn($q)=>$q->where('owner_id',$ownerId))->when($search,function($q)use($search){$q->where(function($i)use($search){$i->where('outlet_name','like',"%{$search}%")->orWhere('code','like',"%{$search}%")->orWhereHas('owner',function($o)use($search){$o->where('brand_name','like',"%{$search}%")->orWhere('name','like',"%{$search}%");});});})->orderByRaw('qris_billing_due_date IS NULL DESC, qris_billing_due_date ASC')->get()->map(fn(Outlet $o)=>$this->decorateOutlet($o,now()->format('Y-m'))); $summary=$this->summary($outlets); $owners=Owner::orderBy('brand_name')->get(); return view('admin.qris_billing.blocked',compact('outlets','ownerId','search','summary','owners')); }

    public function report(Request $request) { $period=$request->input('period'); $ownerId=$request->input('owner_id'); $search=$request->input('search'); $outlets=QrisBillingPayment::with(['outlet.owner.user','outlet.devices'])->where('status','paid')->when($period,fn($q)=>$q->whereRaw("DATE_FORMAT(period_end, '%Y-%m') = ?",[$period]))->whereHas('outlet',function($q)use($ownerId,$search){$q->when($ownerId,fn($x)=>$x->where('owner_id',$ownerId))->when($search,function($x)use($search){$x->where(function($i)use($search){$i->where('outlet_name','like',"%{$search}%")->orWhere('code','like',"%{$search}%")->orWhereHas('owner',function($o)use($search){$o->where('brand_name','like',"%{$search}%")->orWhere('name','like',"%{$search}%");});});});})->latest('paid_at')->get()->map(fn($p)=>$this->outletFromBillingPayment($p))->filter()->values(); $summary=$this->summary($outlets); $owners=Owner::orderBy('brand_name')->get(); $status='paid'; return view('admin.qris_billing.report',compact('outlets','period','status','ownerId','search','summary','owners')); }

    private function outletFromBillingPayment(QrisBillingPayment $payment): ?Outlet { $outlet=$payment->outlet; if(!$outlet)return null; $start=$payment->period_start; $end=$payment->period_end; $period=$start&&$end&&$start->format('Y-m-d')!==$end->format('Y-m-d')?$start->translatedFormat('d F Y').' - '.$end->translatedFormat('d F Y'):($end??$start)?->translatedFormat('d F Y') ?? now()->translatedFormat('F Y'); $outlet->billing_period=$period; $outlet->billing_payment=$payment; $outlet->billing_due_date=$end??$start; $outlet->billing_amount=$payment->amount; $outlet->billing_status=$payment->status; return $outlet; }

    public function show(Request $request, Outlet $outlet) { $period=$request->input('period',now()->format('Y-m')); $outlet->load(['owner.user','owner.withdrawals','devices','qrisBillingPayments']); $outlet=$this->decorateOutlet($outlet,$period); $billingBreakdown=$this->billingBreakdown($outlet); return view('admin.qris_billing.show',compact('outlet','period','billingBreakdown')); }
    public function showPayment(Request $request,QrisBillingPayment $payment) { if($payment->status!=='pending')abort(404,'Data outlet tidak ditemukan.'); $period=$request->input('period',$payment->period_end?->format('Y-m')??$payment->period_start?->format('Y-m')??now()->format('Y-m')); $outlet=$this->outletFromBillingPayment($payment); $outlet->load(['owner.user','owner.withdrawals','devices']); $billingBreakdown=$this->billingBreakdown($outlet); return view('admin.qris_billing.show',compact('outlet','period','billingBreakdown')); }

    public function markPaid(Request $request, Outlet $outlet)
    {
        if(!$outlet->qris_billing_enabled)return back()->with('error','Outlet ini tidak memakai perpanjangan QRIS.');
        $outlet->loadMissing(['devices','qrisBillingPayments']);
        $paymentId=$request->input('payment_id');
        $pendingPayment=$paymentId?$outlet->qrisBillingPayments()->whereKey($paymentId)->where('status','pending')->first():$outlet->latestQrisBillingPayment('pending');

        // Keep the period captured when the owner submitted the payment.
        // Early payment: existing due date -> +1 month. Overdue payment: payment day -> +1 month.
        if($pendingPayment && $pendingPayment->period_start && $pendingPayment->period_end){
            $activeFrom=$pendingPayment->period_start->copy()->startOfDay();
            $activeUntil=$pendingPayment->period_end->copy()->startOfDay();
        } elseif($outlet->qris_billing_prepayment_window){
            $activeFrom=$outlet->qris_billing_due_date->copy()->startOfDay();
            $activeUntil=$activeFrom->copy()->addMonthNoOverflow()->startOfDay();
        } else {
            $activeFrom=now()->copy()->startOfDay();
            $activeUntil=$activeFrom->copy()->addMonthNoOverflow()->startOfDay();
        }

        $data=['period_start'=>$activeFrom->format('Y-m-d'),'period_end'=>$activeUntil->format('Y-m-d'),'amount'=>$pendingPayment?->amount?:$outlet->qris_billing_amount,'status'=>'paid','paid_at'=>now()];
        if($request->hasFile('proof_of_payment'))$data['proof_of_payment']=$request->file('proof_of_payment')->store('qris-billing','public');
        DB::transaction(function()use($outlet,$pendingPayment,$data,$activeUntil){if($pendingPayment)$pendingPayment->update($data);else QrisBillingPayment::create(array_merge(['outlet_id'=>$outlet->id],$data));$outlet->update(['qris_billing_due_date'=>$activeUntil->toDateString()]);});
        return redirect()->route('admin.qris-billing.report')->with('success','Perpanjangan QRIS berhasil dikonfirmasi sampai '.$activeUntil->format('d/m/Y').'.');
    }

    private function baseOutletQuery(){return Outlet::with(['owner.user','devices','qrisBillingPayments'])->withCount('devices')->where('qris_billing_enabled',true);}
    private function decorateOutlet(Outlet $outlet,string $period):Outlet{$unpaidSummary=$outlet->qrisBillingUnpaidSummary();$payment=$outlet->qrisBillingPaymentForTargetDate($period.'-01');$dueDate=$unpaidSummary['oldest_due_date']??$outlet->qrisBillingDueDateForPeriod($period);$isPending=$unpaidSummary['pending_count']>0;$isDue=$unpaidSummary['due_count']>0;$outlet->billing_period=$unpaidSummary['oldest_period']&&$unpaidSummary['latest_period']&&$unpaidSummary['oldest_period']!==$unpaidSummary['latest_period']?$unpaidSummary['oldest_period'].' - '.$unpaidSummary['latest_period']:($unpaidSummary['oldest_period']??$period);$outlet->billing_action_period=$unpaidSummary['latest_period']??$period;$outlet->billing_payment=$payment;$outlet->billing_due_date=$dueDate;$outlet->billing_amount=$unpaidSummary['amount'];$outlet->billing_month_count=$unpaidSummary['count'];$outlet->billing_unpaid_periods=$unpaidSummary['periods'];$outlet->billing_status=$unpaidSummary['count']===0?'paid':($isPending?'pending':($isDue?'due':'not_due'));return $outlet;}
    private function dueDateForPeriod(Outlet $outlet,?string $period):?Carbon{return $outlet->qris_billing_due_date;}
    private function summary($outlets):array{return ['total'=>$outlets->count(),'paid'=>$outlets->where('billing_status','paid')->count(),'pending'=>$outlets->where('billing_status','pending')->count(),'due'=>$outlets->where('billing_status','due')->count(),'amount'=>$outlets->sum('billing_amount'),'paid_amount'=>$outlets->where('billing_status','paid')->sum('billing_amount'),'unpaid_amount'=>$outlets->where('billing_status','!=','paid')->sum('billing_amount')];}
    private function summaryFromPayments($payments):array{return ['total'=>$payments->count(),'pending'=>$payments->count(),'paid'=>0,'due'=>0,'amount'=>$payments->sum('amount'),'paid_amount'=>0,'unpaid_amount'=>$payments->sum('amount')];}
    private function billingBreakdown(Outlet $outlet):array{$breakdown=$outlet->qrisBillingAmountBreakdown();return array_merge($breakdown,['month_count'=>1,'grand_total'=>$outlet->billing_amount,'periods'=>[['period'=>$outlet->billing_due_date?->format('Y-m')??now()->format('Y-m'),'due_date'=>$outlet->billing_due_date??now(),'amount'=>$outlet->billing_amount,'status'=>$outlet->billing_status]]]);}
}
