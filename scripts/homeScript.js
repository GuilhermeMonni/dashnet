//load posts
document.addEventListener('DOMContentLoaded', () => {
    const mainContent = document.querySelector('.main-content')
    const page = window.location.pathname

    if(mainContent){
        const textarea = document.querySelector('#post-content');
        const counter = document.querySelector('.char-counter');
        const form = document.querySelector('.post-form');

        // Count car
        textarea?.addEventListener('input', () => {
            const len = textarea.value.length;
            counter.textContent = `${len}/500`;
            counter.style.color = len > 450 ? 'var(--colorAlert)' : '';
        });

        //event enter
        form?.addEventListener('keypress', (e) =>{
            if(e.key === "Enter"){
                document.querySelector(".btn-publish").click()
            }
        })

        // Submit
        form?.addEventListener('submit', async (e) => {
            e.preventDefault();
            const btn = form.querySelector('.btn-publish');
            btn.disabled = true;
            btn.textContent = 'Publicando...';

            const formData = new FormData(form);

            try {
                const res = await fetch('home.php', { method: 'POST', body: formData });
                const data = await res.json();

                if (data.success) {
                    textarea.value = '';
                    counter.textContent = '0/500';
                    await carregarPosts(page);

                    Swal.fire({
                        icon: 'success',
                        title: 'Publicado!',
                        text: 'Seu post foi publicado com sucesso.',
                        timer: 1800,
                        showConfirmButton: false
                    });
                } else {
                    Swal.fire({ icon: 'error', title: 'Erro', text: data.error });
                }
            } catch {
                Swal.fire({ icon: 'error', title: 'Erro', text: 'Falha na conexão.' });
            } finally {
                btn.disabled = false;
                btn.textContent = 'Publicar';
            }

            await carregarPosts(page)
        })
    }

    carregarPosts(page)
})
async function carregarPosts(page) {
    const container = document.getElementById('posts-container')

    try {
        let url = ''

        if (page.includes('home.php')) {
            url = 'getPosts.php'
        } else if (page.includes('perfil.php')) {
            url = 'getPostsProfile.php'
        } 

        const res = await fetch(url)

        if (!res.ok) {
            throw new Error(`Erro HTTP: ${res.status}`)
        }

        const data = await res.json();

        if (!data.posts || data.posts.length === 0) {
            container.innerHTML = page.includes('perfil.php')
                ? 'Publique seu primeiro post 😄'
                : 'Seja o primeiro a publicar um post 😄'

            container.classList.add('posts-empty')
            return
        }

        container.classList.remove('posts-empty');
        container.innerHTML = data.posts.map(post => `
            <article class="post-card">
                <div class="post-header">
                    <div class="post-avatar">${avatar(post.nome)}</div>
                    <div>
                        <strong class="post-author">${escapar(post.nome)}</strong>
                        <span class="post-date">${formatarData(post.created_at)}</span>
                    </div>
                </div>
                <p class="post-content">${escapar(post.content)}</p>
            </article>
        `).join('')
    } catch (erro) {
        console.error('Erro ao carregar posts:', erro)
        container.innerHTML = 'Erro ao carregar os posts.'
        container.classList.add('posts-empty')
    }
}

function logout() { //popup logout
    Swal.fire({
        title: 'Deseja sair?',
        text: 'Você será desconectado do sistema',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, sair',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: 'var(--colorAlert)',
        cancelButtonColor: '#6c757d'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = 'logout.php';
        }
    });
}

function avatar(nome) { //avatar user
 return nome.trim().split(' ').map(p => p[0]).slice(0, 2).join('').toUpperCase();
}

function escapar(str) { //clear text
    const d = document.createElement('div');
    d.textContent = str;
    return d.innerHTML;
}

function formatarData(dt) {
    const d = new Date(dt);
    return d.toLocaleString('pt-BR', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function perfil(){
    window.location.href = 'perfil.php'
}

function home(){
    window.location.href = 'home.php'
}

function bioCounter() {
    const bio = document.getElementById('bio')
    const counter = document.getElementById('bio-counter')
    counter.textContent = `${bio.value.length}/80`
}