


<!DOCTYPE html>
<html>
<head>
    <!-- ... -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <!-- ... -->
</head>
<body>
    <form action="{{ route('loan.store') }}" method="POST">
        @csrf
        <label for="rfid">Search RFID:</label>
        <input type="text" id="rfid" name="rfid" required>
    
        <label for="book">Select Book:</label>
        <select id="book" name="book" required>
            <option value="">Select a book</option>
            @foreach (App\Models\Book::all() as $book)
                <option value="{{ $book->id }}">{{ $book->title }}</option>
            @endforeach
        </select>
    
        <button type="submit">Search</button>
    </form>
    
    <script>
        document.getElementById('rfid').addEventListener('input', function() {
            var rfid = this.value;
    
            // Mengirim permintaan fetch ke API
            fetch('{{ route('check.rfid') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({ rfid: rfid })
            })
            .then(function(response) {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(function(data) {
                // Memproses respons dari API
                console.log(data);
            })
            .catch(function(error) {
                // Menangani kesalahan saat mengirim permintaan fetch
                console.error(error);
            });
        });
    </script>
</body>
</html>
