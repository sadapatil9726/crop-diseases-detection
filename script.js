const container = document.querySelector('.container');
const LoginLink = document.querySelector('.SignInLink');
const RegisterLink = document.querySelector('.SignUpLink');

if (RegisterLink && container) {
    RegisterLink.addEventListener('click', (e) =>{
        e.preventDefault();
        container.classList.add('active');
    });
}

if (LoginLink && container) {
    LoginLink.addEventListener('click', (e) => {
        e.preventDefault();
        container.classList.remove('active');
    });
}

async function handleResponse(res){
    let data;
    try { data = await res.json(); } catch(_) { alert('Unexpected server response'); return; }
    if (data.ok) {
        alert(data.message || 'Success');
        if (data.redirect) {
            window.location.href = data.redirect;
        }
    } else {
        alert(data.message || 'Action failed');
    }
}

// Register
const regForm = document.querySelector('.Register form');
if (regForm) {
    regForm.onsubmit = async function(e){
        e.preventDefault();
        const fd = new FormData(regForm);
        fd.append('action','register');
        const res = await fetch('auth.php', {method:'POST', body:fd});
        await handleResponse(res);
    }
}

// Login
const loginForm = document.querySelector('.Login form');
if (loginForm) {
    loginForm.onsubmit = async function(e){
        e.preventDefault();
        const fd = new FormData(loginForm);
        fd.append('action','login');
        const res = await fetch('auth.php', {method:'POST', body:fd});
        await handleResponse(res);
    }
}
