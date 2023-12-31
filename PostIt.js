
window.addEventListener("DOMContentLoaded", (ev)=>{
    const url = window.location.href;
    const startlimitX = 0;
    const startlimitY = 0;
    let endlimitX = window.innerWidth;
    let endlimitY =  window.innerHeight;
    let allEvents = [];
    const posts = document.querySelectorAll('.post-it-window');

    window.addEventListener('resize', ()=>{
        endlimitX = window.innerWidth;
        endlimitY = window.innerHeight;
    })

    const validateHead = (e) =>{
        return e.type == "characterData" && e.target!="\"\"" && e.target.textContent!="" && e.target.parentNode!=null && e.target.parentNode.tagName=='H4';
    } 

    const validateBody = (e) =>{
        return e.type == "characterData" && e.target!="\"\"" && e.target.textContent!="" && e.target.parentNode!=null && e.target.parentNode.tagName=='P';
    }
   
    /**
     * Event loop system
     */
    setInterval(async()=>{
        const actualEvents = allEvents.length;
        if(allEvents.length>0 && actualEvents == allEvents.length){
            const event = allEvents.pop();
            console.log(event)
            const body = new FormData();
            body.append('event', JSON.stringify(event))
            await fetch(url+'/editNote', {method: 'POST', body: body})
            .then(res=>{
                allEvents = [];
            })
            .catch(err=>{
                console.error(err); 
                allEvents=[]});
        }
    }, 1000);
    //Observer have one mission that is mdeling the data of every event which concerns at text, size and postion modification
    const osberver =  new MutationObserver((ev)=>{
        for(let e of ev){
            let id = 0;
            let innertext = '';
            let header = '';
            let styles = '';
            if(validateHead(e)){
                header = e.target.textContent.trim();
                id = e.target.parentElement.parentElement.parentElement.dataset.id;    
                
            }
            if(validateBody(e)){
                innertext = e.target.textContent.trim();
                id = e.target.parentElement.parentElement.parentElement.dataset.id;               
                
            }
            if(e.type == 'attributes' && e.target.tagName=='DIV'){
                styles = e.target.attributes.style.textContent;
                id = e.target.dataset.id;
                
            }
            allEvents.push({id, innertext, header, styles})
        }
      })                                  
    
    for(const [i, post] of posts.entries()){
        let startX = 0;
        let startY = 0;
        let isDragging = false;
        function startDrag(e) {  
            e.stopPropagation();
            e.preventDefault();
            if(post.parentElement.style.top == '0px' 
                || post.parentElement.style.left == '0px' ){
                startX =post.parentElement.getBoundingClientRect().x;
                startY =post.parentElement.getBoundingClientRect().y;
            }
            isDragging = true;
        }
        function drag(e) {
            e.stopPropagation();
            e.preventDefault();
            if (isDragging) {
                x = e.clientX-startX-10;
                y = e.clientY-startY-10;
                post.parentElement.style.left = x+'px';
                post.parentElement.style.top = y+'px';
                if(x < startlimitX) post.parentElement.style.left = '0px';     
                if(y < startlimitY) post.parentElement.style.top = '0px';     
                if(x > endlimitX-post.parentElement.style.width.replace('px', '')) post.parentElement.style.left = endlimitX-parseInt(post.parentElement.style.width.replace('px', ''))+'px';     
                if(y > endlimitY-parseInt(post.parentElement.style.height.replace('px', ''))) post.parentElement.style.top = endlimitY-parseInt(post.parentElement.style.height.replace('px', ''))+'px';          
            }
        }
        function stopDrag(e) { 
            e.stopPropagation();
            e.preventDefault();
            isDragging = false;
        }

        function minimize () {
            post.parentElement.style.height = '2.5rem';
        }

        function close (ev) {
            post.parentElement.remove();
        }

        async function add(e){
            const conn = await fetch(url+'/addNote');
            const json = await conn.json();
            if(json.status == 200){
                window.location.reload();
            }
        }
        async function remove(e){
            const form = new FormData();
            form.append('id', post.parentElement.dataset.id);
            const conn = await fetch(url+'/removeNote',{
                method: "POST",
                body: form
            });
            const json = await conn.json();
            if(json.status == 200){
                window.location.reload();                    
            }
        }
        post.addEventListener('dblclick', (e)=>{
            const arrayChildren = [... post.children]
            if(!arrayChildren.some((e)=>e.type=='color')){
                const color = document.createElement('input');
                color.type = "color";
                color.id="color";
                color.style.width = '2rem'
                color.style.height = '2.5rem'
                color.style.border = '0px'
                color.style.padding = '0px'
                color.value = post.parentElement.style.backgroundColor;
                color.dataset.id = post.parentElement.dataset.id;
                color.style.backgroundColor = post.parentElement.style.backgroundColor;
                post.appendChild(color)
                color.addEventListener('change', (ev)=>{
                    post.parentElement.style.backgroundColor = ev.target.value;
                    color.style.backgroundColor = post.parentElement.style.backgroundColor;
                })
            }
        })
        // Events whose function is move the notes
        post.addEventListener('mousedown', startDrag);
        post.parentElement.children[1].children[0].addEventListener('contextmenu', (e)=>{
            e.preventDefault()
            //here will be text edition options in header
        })
        post.parentElement.children[1].children[1].addEventListener('contextmenu', (e)=>{
            e.preventDefault()
            //here will be text edition options in body
        })
        window.addEventListener('mouseup', stopDrag);
        window.addEventListener('mousemove', drag);
        post.parentElement.addEventListener('keyup', (e)=>{
            let id = e.target.parentElement.parentElement.dataset.id;
            let innertext = '';
            let header = '';
            let styles = '';
            if(e.key == 'Control' && e.target.tagName == 'H4' && e.target.innerText.length<100){
                header = e.target.innerText;         
                allEvents.push({id, innertext, header, styles})
            }
            if(e.key == 'Control' && e.target.tagName == 'P'){
                innertext = e.target.innerText;
                allEvents.push({id, innertext, header, styles})
            }
        })
        // Event whose function is alterete notes, like minimaze, add more notes, delete notes, or make it invisible
        if(post.children.length == 4){
            post.children[0].addEventListener('click', remove);
            post.children[1].addEventListener('click', add);
            post.children[3].addEventListener('click', close);
            post.children[2].addEventListener('click', minimize);
        }else{
            post.children[0].addEventListener('click', add);
            post.children[2].addEventListener('click', close);
            post.children[1].addEventListener('click', minimize);
        }
        //Add postit to event loop, because the observer will add all internal events in a list where every 2 seconds 
        //emits to backend if the list change its size
        osberver.observe(post.parentElement, {
            attributes: true,
            childList : true,
            characterData : true,
            subtree : true,
            attributeOldValue : true,
            characterDataOldValue : true,
            attributeFilter : ['tagName','style', 'innerText', 'nodeText'],
        });
    }
})  
