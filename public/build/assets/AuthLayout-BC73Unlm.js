import{r as o,j as e,L as t}from"./app-DoChvkHT.js";import{C as r}from"./CookieBanner-Cl-G1Oqp.js";function m({children:a,image:s,title:i,subtitle:n}){return o.useEffect(()=>(document.body.classList.add("auth-redesign"),()=>document.body.classList.remove("auth-redesign")),[]),e.jsxs("div",{className:"auth-layout-container min-vh-100 w-100 overflow-hidden row m-0 p-0",children:[e.jsxs("div",{className:"auth-image-side col-lg-6 d-none d-lg-block position-relative p-0",style:{backgroundImage:`url(${s})`,backgroundSize:"cover",backgroundPosition:"center",backgroundRepeat:"no-repeat",minHeight:"100vh"},children:[e.jsx("div",{className:"auth-overlay-main position-absolute top-0 left-0 w-100 h-100",style:{background:"linear-gradient(rgba(0,0,0,0.2) 0%, rgba(0,0,0,0.7) 100%)",zIndex:1}}),e.jsxs("div",{className:"auth-content-box position-absolute bottom-0 start-0 w-100 p-5 text-white",style:{zIndex:2},children:[e.jsx("h2",{className:"display-4 fw-bold mb-3 text-white",children:i}),e.jsx("p",{className:"lead fw-medium text-white opacity-90",children:n})]}),e.jsxs(t,{href:"/",className:"position-absolute top-0 start-0 m-4 text-white text-decoration-none d-flex align-items-center fw-bold small hover-opacity transition-all",style:{zIndex:10,textShadow:"0 2px 4px rgba(0,0,0,0.5)"},children:[e.jsx("i",{className:"fas fa-chevron-left me-2"}),"RETURN TO HOME"]})]}),e.jsx("div",{className:"auth-form-side col-12 col-lg-6 bg-white d-flex align-items-center justify-content-center p-4 p-md-5 min-vh-100",children:e.jsxs("div",{className:"auth-form-wrapper w-100",style:{maxWidth:"440px"},children:[e.jsx("div",{className:"d-lg-none mb-4",children:e.jsxs(t,{href:"/",className:"text-primary text-decoration-none fw-bold small",children:[e.jsx("i",{className:"fas fa-arrow-left me-2"})," HOME"]})}),e.jsx("div",{className:"auth-form-logo mb-4 text-center align-items-center justify-content-center",children:e.jsx("img",{src:"/assets/img/logo/Logo2-transparent.png",alt:"Nyalife HMS",className:"mb-5 align-items-center justify-content-center",height:"50px"})}),e.jsx("div",{className:"auth-form-container",children:a}),e.jsxs("div",{className:"auth-footer mt-5 text-center text-muted small",children:[e.jsxs("p",{className:"mb-0",children:["© ",new Date().getFullYear()," Nyalife Women's Clinic."]}),e.jsx("p",{className:"opacity-50",children:"All rights reserved."})]})]})}),e.jsx(r,{}),e.jsx("style",{children:`
                .auth-redesign { overflow-x: hidden; }
                .hover-opacity:hover { opacity: 0.8; }
                .transition-all { transition: all 0.3s ease; }
                .auth-form-wrapper { animation: fadeInScale 0.6s cubic-bezier(0.16, 1, 0.3, 1); }
                @keyframes fadeInScale {
                    from { opacity: 0; transform: translateY(15px) scale(0.98); }
                    to { opacity: 1; transform: translateY(0) scale(1); }
                }

                .auth-form-logo {
                    width: 100%;
                    height: auto;
                    align-items: center;
                    justify-content: center;
                }

                .auth-form-logo img {
                    height: 70px;
                }

                @media (max-width: 991.98px) {
                    .auth-image-side { display: none; }
                    .auth-form-side { min-height: 100vh; }
                }
            `})]})}export{m as A};
