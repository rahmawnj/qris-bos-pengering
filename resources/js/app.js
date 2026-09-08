import axios from 'axios';
import './bootstrap';

const messageElement = document.getElementById('messageOutput')
const userMessageInput = document.getElementById('message')
const sendMessageForm = document.getElementById('chatForm')

let url = window.location;
let urlNew = new URL(url)
let userName = urlNew.searchParams.get('name')

sendMessageForm.addEventListener('submit', function(e){
    e.preventDefault()
    if (userMessageInput.value != '') {
        axios({
            method: 'post',
            url: '/sendMessage',
            data: {
                username: 'jijijiji',
                message: 'jkjk'
            }
        })
        
    }

    window.Echo.channel('laravelChat')
    .listen('.chatting', (res) => {
        console.log(res)
    })
})