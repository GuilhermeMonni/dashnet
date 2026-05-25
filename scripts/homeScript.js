//load posts
document.addEventListener("DOMContentLoaded", () => {
  const mainContent = document.querySelector(".main-content");
  const page = window.location.pathname;

  if (mainContent) {
    const textarea = document.querySelector("#post-content");
    const counter = document.querySelector(".char-counter");
    const form = document.querySelector(".post-form");

    // Count car
    textarea?.addEventListener("input", () => {
      const len = textarea.value.length;
      counter.textContent = `${len}/500`;
      counter.style.color = len > 450 ? "var(--colorAlert)" : "";
    });

    //event enter
    form?.addEventListener("keypress", (e) => {
      if (e.key === "Enter") {
        document.querySelector(".btn-publish").click();
      }
    });

    // Submit
    form?.addEventListener("submit", async (e) => {
      e.preventDefault();
      const btn = form.querySelector(".btn-publish");
      btn.disabled = true;
      btn.textContent = "Publicando...";

      const formData = new FormData(form);

      try {
        const res = await fetch("home.php", { method: "POST", body: formData });
        const data = await res.json();

        if (data.success) {
          textarea.value = "";
          counter.textContent = "0/500";
          await carregarPosts(page);

          Swal.fire({
            icon: "success",
            title: "Publicado!",
            text: "Seu post foi publicado com sucesso.",
            timer: 1800,
            showConfirmButton: false,
          });
        } else {
          Swal.fire({ icon: "error", title: "Erro", text: data.error });
        }
      } catch {
        Swal.fire({ icon: "error", title: "Erro", text: "Falha na conexão." });
      } finally {
        btn.disabled = false;
        btn.textContent = "Publicar";
      }

      await carregarPosts(page);
    });
  }

  carregarPosts(page);
});
async function carregarPosts(page) {
  //load the posts
  const container = document.getElementById("posts-container");

  try {
    let url = "";

    if (page.includes("home.php")) {
      url = "getPosts.php";
    } else if (page.includes("perfil.php")) {
      url = "getPostsProfile.php";
    }

    const res = await fetch(url);

    if (!res.ok) {
      throw new Error(`Erro HTTP: ${res.status}`);
    }

    const data = await res.json();

    if (!data.posts || data.posts.length === 0) {
      container.innerHTML = page.includes("perfil.php")
        ? "Publique seu primeiro post 😄"
        : "Seja o primeiro a publicar um post 😄";

      container.classList.add("posts-empty");
      return;
    }

    const post = await Promise.all(
      data.posts.map(async (post) => {
        //view likes and comments post
        const getLikes = await getLikesCount(post.idContent);
        const getComments = await getCommentsCount(post.idContent);
        post.comments = getComments.comments;
        post.commentsCount = getComments.commentsCount;
        post.likes = getLikes.likes_count;
        post.liked = getLikes.liked;
        console.log(post);

        return post;
      }),
    );

    container.classList.remove("posts-empty");
    container.innerHTML = post
      .map(
        (post) => `
            <article class="post-card">
              <div class="post-header">
                  <div class="post-avatar">${avatar(post.nome)}</div>
                  <div>
                      <strong class="post-author">${escapar(post.nome)}</strong>
                      <span class="post-date">${formatarData(post.created_at)}</span>
                  </div>
              </div>

              <p class="post-content">${escapar(post.content)}</p>

              <div class="comment-box">
                  <label for="commentInput-${post.idContent}" class="sr-only">Escreva um comentário</label>

                  <textarea
                      name="commentInput-${post.idContent}"
                      id="commentInput-${post.idContent}"
                      class="comment-textarea"
                      placeholder="Escreva um comentário..."
                      maxlength="500"
                      rows="2"
                  ></textarea>

                  <div class="btn-actions">
                      <button class="comment-submit-btn" type="button" onclick="sendComment(${post.idContent}, this)">
                          Enviar
                      </button>

                      <button class="post-action-btn" type="button" aria-label="Curtir post" onclick="like(${post.idContent}, this)">
                          <span class="like-count">${Number(post.likes) || 0}</span>
                          <i class="bi ${post.liked == 1 ? "bi-heart-fill" : "bi-heart"}"
                            style="${post.liked == 1 ? "color: var(--colorAlert);" : "color: var(--colorBlack);"}"></i>
                      </button>

                      <button class="post-action-btn" type="button" onclick="document.getElementById('commentInput-${post.idContent}').focus()">
                          <span class="comment-count">${Number(post.commentsCount) || 0}</span>
                          <i class="bi bi-chat-left-text"></i>
                      </button>
                  </div>
              </div>
              <div class="post-comments" id="postComments-${post.idContent}">
                <div class="post-comments-header" style="${post.comments && post.commentsCount ? '' : 'display: none;'}">
                    <strong>Comentários</strong>
                </div>

                <div class="post-comments-list">
                    ${post.comments && post.commentsCount
                        ? post.comments.map(comment => `
                            <div class="post-comment-item">
                                <div class="post-comment-avatar">${avatar(comment.userName)}</div>
                                <div class="post-comment-body">
                                    <div class="post-comment-data">
                                        <strong class="post-comment-author">${escapar(comment.userName)}</strong>
                                        <span class="post-comment-date">${formatarData(comment.date)}</span>
                                    </div>
                                    <p class="post-comment-content">${escapar(comment.content)}</p>
                                </div>
                            </div>
                        `).join('')
                        : ''
                    }
                </div>
            </div>
          </article>
        `,
      )
      .join("");
  } catch (erro) {
    console.error("Erro ao carregar posts:", erro);
    container.innerHTML = "Erro ao carregar os posts.";
    container.classList.add("posts-empty");
  }
}

async function like(postId, button) {
  //btn like
  const page = window.location.pathname;
  try {
    const res = await fetch("likePost.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({ post_id: postId }),
    });

    const data = await res.json();

    if (!data.success) {
      throw new Error(data.error || "Erro ao curtir");
    }

    const icon = button.querySelector("i");

    if (icon.classList.contains("bi-heart")) {
      icon.classList.replace("bi-heart", "bi-heart-fill");
      icon.style.color = "var(--colorAlert)";
      await getLikesCount(postId);
      await carregarPosts(page);
    } else {
      icon.classList.replace("bi-heart-fill", "bi-heart");
      icon.style.color = "var(--colorBlack)";
      await getLikesCount(postId);
      await carregarPosts(page);
    }
  } catch (erro) {
    console.error(erro);
  }
}
async function getLikesCount(postId) {
  try {
    const res = await fetch(`likePost.php?post_id=${postId}`);
    const data = await res.json();

    if (data.success) {
      return data;
    }

    return 0;
  } catch (erro) {
    console.error("Erro ao buscar likes:", erro);
    return 0;
  }
}

async function sendComment(postId, btn) {
  //comment the post
  const page = window.location.pathname;
  const textarea = document.getElementById(`commentInput-${postId}`);
  const comment = textarea.value.trim();

  if (!comment) return;

  try {
    const res = await fetch("commentPost.php", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
      },
      body: JSON.stringify({
        post_id: postId,
        content: comment,
      }),
    });

    const data = await res.json();

    if (!data.success) {
      throw new Error(data.error || "Erro ao comentar");
    }

    console.log("Comentário salvo com sucesso: ", comment);
    await getCommentsCount(postId);
    await carregarPosts(page);
  } catch (erro) {
    console.error(erro);
  }
}
async function getCommentsCount(postId) {
  try {
    const res = await fetch(`commentPost.php?post_id=${postId}`);
    const data = await res.json();

    if (!data.success || !data.comments_content) {
      return {
        commentsCount: 0,
        comments: [],
      };
    }

    const commentsWithUsername = await Promise.all(
      data.comments_content.map(async (comment) => {
        try {
          const commentUsername = await fetch(
            `getUsername.php?userId=${comment.user_id}`,
          );
          const dataUserName = await commentUsername.json();

          return {
            id: comment.id,
            userName: dataUserName.user_name
              ? dataUserName.user_name
              : "Usuário indisponível.",
            userId: comment.user_id,
            content: comment.content,
            date: comment.created_at,
          };
        } catch (error) {
          console.error("Erro ao buscar comentários!", error);
        }
      }),
    );

    return {
      commentsCount: data.comments_content.length,
      comments: commentsWithUsername,
    };
  } catch (erro) {
    console.error("Erro ao buscar comentários:", erro);
    return {
      commentsCount: 0,
      comments: [],
    };
  }
}

function logout() {
  //popup logout
  Swal.fire({
    title: "Deseja sair?",
    text: "Você será desconectado do sistema",
    icon: "question",
    showCancelButton: true,
    confirmButtonText: "Sim, sair",
    cancelButtonText: "Cancelar",
    confirmButtonColor: "var(--colorAlert)",
  }).then((result) => {
    if (result.isConfirmed) {
      window.location.href = "logout.php";
    }
  });
}

function avatar(nome) {
  //avatar user
  return nome
    .trim()
    .split(" ")
    .map((p) => p[0])
    .slice(0, 2)
    .join("")
    .toUpperCase();
}

function escapar(str) {
  //clear text
  const d = document.createElement("div");
  d.textContent = str;
  return d.innerHTML;
}

function formatarData(dt) {
  const d = new Date(dt);
  return d.toLocaleString("pt-BR", {
    day: "2-digit",
    month: "2-digit",
    year: "numeric",
    hour: "2-digit",
    minute: "2-digit",
  });
}

function perfil() {
  //btn go profile
  window.location.href = "perfil.php";
}

function home() {
  //btn go home
  window.location.href = "home.php";
}

function bioCounter() {
  //count caracter bio
  const bio = document.getElementById("bio");
  const counter = document.getElementById("bio-counter");
  counter.textContent = `${bio.value.length}/80`;
}
