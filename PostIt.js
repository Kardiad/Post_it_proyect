
window.addEventListener("DOMContentLoaded", (ev)=>{
    const url = window.location.href;
    const osberver =  new MutationObserver((ev)=>{
        
        /**
         * Misiones del observer: 
         *  1º Ajustar la altura del elemento html al 
         * límite del borde de la página, es decir, nunca será ni 
         * mayor a 0 ni mayor al ancho-alto
         *  2º Mandar el tamaño de la not
         *  3º Mandar el texto titular de la notas
         *  4º Mandar el texto de la nota
         * 
         */      
        //if(ev[0].type == "characterData")
        for(let e of ev){
            if(e.type == "characterData" && e.target!="\"\"" && e.target.parentNode!=null && e.target.parentNode.tagName=='H4'){
                const text = e.target;
                const id = e.target.parentElement.parentElement.parentElement.dataset.id;
                console.log(id);
                const body = new FormData();
                body.append('header', text);
                body.append('id', id);
                
            }
        }
      })                                  
    const posts = document.querySelectorAll('.post-it-window');
    const postsContainer = document.querySelector('.post-it').parentElement;
        /**
         * Eventos que faltan por tratar:
         *  1º Paredes, es decir, que no se salga de las paredes establecidas
         *  2º En caso de scroll que tome la altura de más que ha pillado, para que el e.clientY/X no se vaya de madre
         */
        for(const [i, post] of posts.entries()){
            let startX = 0;
            let startY = 0;
            let isDragging = false;
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
            // Agregamos los eventos al elemento
           post.addEventListener('mousedown', startDrag);
           window.addEventListener('mouseup', stopDrag);
           window.addEventListener('mousemove', drag);
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
            //Damos de alta a los elementos en el observer
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
