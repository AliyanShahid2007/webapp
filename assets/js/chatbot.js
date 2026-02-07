// Chatbot frontend behavior: toggling, sending messages, rendering responses
(function(){
    const toggle = document.getElementById('chatbot-toggle');
    const panel = document.getElementById('chatbot-panel');
    const closeBtn = document.getElementById('chatbot-close');
    const form = document.getElementById('chatbot-form');
    const input = document.getElementById('chatbot-input');
    const messages = document.getElementById('chatbot-messages');

    function appendMessage(text, who='bot'){
        const wrap = document.createElement('div');
        wrap.className = 'chatbot-msg ' + (who==='user'? 'user':'bot');
        const bubble = document.createElement('div');
        bubble.className = 'bubble';
        bubble.textContent = text;
        wrap.appendChild(bubble);
        messages.appendChild(wrap);
        messages.scrollTop = messages.scrollHeight;
    }

    function setTyping(on){
        let el = document.getElementById('chatbot-typing');
        if(on){
            if(!el){
                el = document.createElement('div');
                el.id = 'chatbot-typing';
                el.className = 'chatbot-typing';
                el.textContent = 'Assistant is typing...';
                messages.appendChild(el);
            }
        } else if(el){
            el.remove();
        }
        messages.scrollTop = messages.scrollHeight;
    }

    toggle.addEventListener('click', ()=>{
        panel.classList.toggle('d-none');
    });
    closeBtn.addEventListener('click', ()=> panel.classList.add('d-none'));

    form.addEventListener('submit', async (e)=>{
        e.preventDefault();
        const text = input.value.trim();
        if(!text) return;
        appendMessage(text, 'user');
        input.value = '';
        setTyping(true);

        try{
            const resp = await fetch(window.basePath + '/api/chat.php', {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({message: text})
            });
            const data = await resp.json();
            if(data && data.reply){
                appendMessage(data.reply, 'bot');
            } else {
                appendMessage('Sorry, the assistant could not answer right now.');
            }
        }catch(err){
            appendMessage('Network error: ' + (err.message||err));
        }finally{
            setTyping(false);
        }
    });

    // Optional: welcome message
    appendMessage('Hi! I can answer questions about this site. Try asking about gigs, profiles, or ordering.');
})();
