
window.addEventListener("DOMContentLoaded", (ev)=>{
    const url = window.location.href;
    let allEvents = [];
    /**
     * Event loop system
     */
    setInterval(async()=>{
        const actualEvents = allEvents.length;
        if(allEvents.length>0 && actualEvents == allEvents.length){
            const event = allEvents.pop();
            const body = new FormData();
            body.append('event', JSON.stringify(event))
            const conn = await fetch(url+'/editNote', {method: 'POST', body: body});
            console.log(await conn.json());
            /*const conn = await fetch(url+'/editNote', {
                method:'POST',
                body: body
            });
            const json = await conn.json();
            console.log(json);*/
            allEvents = [];
        }
    }, 3000);
    //Observer have one mission that is mdeling the data of every event which concerns at text, size and postion modification
    const osberver =  new MutationObserver((ev)=>{
        for(let e of ev){
            let id = 0;
            let innertext = '';
            let header = '';
            let styles = '';
            if(e.type == "characterData" && e.target!="\"\"" && e.target.parentNode!=null && e.target.parentNode.tagName=='H4'){
                header = e.target.textContent.trim();
                id = e.target.parentElement.parentElement.parentElement.dataset.id;    
            }
            if(e.type == "characterData" && e.target!="\"\"" && e.target.parentNode!=null && e.target.parentNode.tagName=='P'){
                innertext = e.target.textContent.trim();
                id = e.target.parentElement.parentElement.parentElement.dataset.id;               
            }
            if(e.type == 'attributes'){
                styles = e.target.attributes.style.textContent;
                id = e.target.dataset.id;
            }
            allEvents.push({id, innertext, header, styles})
        }
      })                                  
    const posts = document.querySelectorAll('.post-it-window');
    //const postsContainer = document.querySelector('.post-it').parentElement;
        /**
         * Events that I am still working:
         *  1º Walls, the notes can't pass though the limits of navigator, it is important to don't lose the first note, or other that you don't want anymore
         *  2º Scrollin, if you scrolling down, the notes movement will alterated. My first aproximation is paginate the window and set the correct offset
         */
        for(const [i, post] of posts.entries()){
            let startX = 0;
            let startY = 0;
            let isDragging = false;
            function h4Update(e){
                intervalPromise().then((res)=>{
                    let finalText = allEvents.find((element)=>element.target == text);
                    console.log(finalText.target);
                    /*lastEventPromise(allEvents).then((res)=>{
                    })*/
                });
            }
            function startDrag(e) {  
                e.stopPropagation();
                e.preventDefault();
                if(post.parentElement.style.top == '0px' || post.parentElement.style.left == '0px' ){
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
                }
            }
            function stopDrag(e) { 
                e.stopPropagation();
                e.preventDefault();
                isDragging = false;
            }
    
            function minimize (ev) {
                ev.target.parentElement.parentElement.style.height = '1rem';
            }
    
            function close (ev) {
                ev.target.parentElement.parentElement.remove();
            }
    
            async function add(e){
                const conn = await fetch(url+'/addNote');
                const json = await conn.json();
                if(json.status == 200){
                    window.location.reload();
                }
            }
            async function remove(e){
                console.log(url)
                const form = new FormData();
                form.append('id', post.parentElement.dataset.id);
                console.log(post.parentElement.dataset.id);
                const conn = await fetch(url+'/removeNote',{
                    method: "POST",
                    body: form
                });
                const json = await conn.json();
                if(json.status == 200){
                    window.location.reload();                    
                }
            }
           
            // Events whose function is move the notes
           post.addEventListener('mousedown', startDrag);
           window.addEventListener('mouseup', stopDrag);
           window.addEventListener('mousemove', drag);
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
