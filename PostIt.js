console.log(window)
window.addEventListener("DOMContentLoaded", (ev)=>{
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
        //console.log(ev[0])
      })                                  
    const posts = document.querySelectorAll('.post-it-window');
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
                const conn = await fetch('');
            }
            // Agregamos los eventos al elemento
           post.addEventListener('mousedown', startDrag);
           window.addEventListener('mouseup', stopDrag);
           window.addEventListener('mousemove', drag);
           post.children[0].addEventListener('click', add);
           post.children[2].addEventListener('click', close);
           post.children[1].addEventListener('click', minimize);
            //Damos de alta a los elementos en el observer
            osberver.observe(post.parentElement, {
                attributes: true,
                childList : true,
                characterData : true,
                subtree : true,
                attributeOldValue : true,
                characterDataOldValue : true,
                attributeFilter : ['style', 'innerText', 'nodeText'],
            });
        }
    })  
