import{j as e,L as a}from"./app-BHm_kqjK.js";function d({cms:i,isLoggedIn:s}){const o=[i.hero_slide_1||"/assets/img/slider/slider-1.jpg",i.hero_slide_2||"/assets/img/slider/slider-2.jpg",i.hero_slide_3||"/assets/img/slider/slider-3.jpg",i.hero_slide_4||"/assets/img/slider/slider-4.jpg"],n=[{title:"Obstetrics Care Services",icon:"fa-user-md"},{title:"Gynecology Services",icon:"fa-heartbeat"},{title:"Laboratory Services",icon:"fa-microscope"},{title:"Pharmacy Services",icon:"fa-pills"}],l=t=>t?t.startsWith("cms/")||!t.startsWith("/")?`/storage/${t.replace(/^\/storage\//,"").replace(/^cms\//,"cms/")}`:t:"";return e.jsxs("section",{className:"hero-modern min-vh-75 w-100 position-relative d-flex align-items-center py-2",children:[e.jsxs("div",{className:"hero-background-wrapper position-absolute w-100 h-100 top-0 start-0",children:[o.map((t,r)=>e.jsx("div",{className:`hero-slide position-absolute w-100 h-100 ${r===0?"active":""}`,style:{backgroundImage:`url(${l(t)})`,backgroundSize:"cover",backgroundPosition:"center",zIndex:1}},`slide-${r}`)),e.jsx("div",{className:"hero-contrast-overlay position-absolute w-100 h-100 top-0 start-0",style:{zIndex:2}})]}),e.jsx("div",{className:"container position-relative py-4 px-3 px-md-5",style:{zIndex:3},children:e.jsx("div",{className:"row align-items-center",children:e.jsx("div",{className:"col-lg-10 text-start text-white",children:e.jsxs("div",{className:"hero-content",children:[e.jsx("div",{className:"mb-5",children:e.jsx("span",{className:"badge bg-pink-100 text-pink-600 px-4 py-3 rounded-pill fw-bolder text-uppercase tracking-wider d-inline-block",children:"Specialized Obstetrics & Gynecology Care"})}),e.jsx("h1",{className:"hero-main-title fw-bold text-white mb-4",children:i.hero_title||"Nyalife HMS"}),e.jsx("div",{className:"row g-2 mb-2 mt-2 border-top border-bottom border-white border-opacity-10",children:n.map((t,r)=>e.jsx("div",{className:"col-6 col-md-3",children:e.jsx("div",{className:"hero-card-v4 position-relative rounded-4 overflow-hidden hover-lift transition-all p-1",children:e.jsxs("div",{className:"hero-card-content d-flex flex-column align-items-center justify-content-center text-center py-1 px-2 px-md-4",children:[e.jsx("div",{className:"hero-card-icon-pink mb-2 mb-md-4",children:e.jsx("i",{className:`fas ${t.icon}`,style:{fontSize:"2rem"}})}),e.jsx("span",{className:"lh-sm badge bg-pink-100 text-pink-600 px-3 px-md-4 py-2 rounded-pill fw-bold tracking-tight d-inline-block",style:{fontSize:".7rem"},children:t.title})]})})},`card-${r}`))}),e.jsx("div",{className:"d-flex flex-wrap gap-2 mt-3",children:s?e.jsxs(a,{href:route("dashboard"),className:"btn btn-secondary btn-md px-4 py-3 fw-bold rounded-pill shadow-lg hover-lift",children:[e.jsx("i",{className:"fas fa-tachometer-alt me-2"})," Go to Dashboard"]}):e.jsxs(e.Fragment,{children:[e.jsxs("a",{href:"#appointment",className:"btn btn-secondary btn-md px-4 py-3 fw-bold rounded-pill shadow-lg hover-lift",children:[e.jsx("i",{className:"fas fa-calendar-check me-2"})," Book Appointment"]}),e.jsxs(a,{href:route("login.patient"),className:"btn btn-light btn-md px-4 py-3 fw-bold rounded-pill shadow-lg hover-lift text-dark",children:[e.jsx("i",{className:"fas fa-sign-in-alt me-2"})," Patient Portal"]})]})})]})})})}),e.jsx("style",{children:`
                .hero-modern { background-color: #000; overflow: hidden; }
                .hero-slide { opacity: 0; transition: opacity 1.5s ease-in-out; }
                .hero-slide.active { opacity: 1; }
                
                .hero-contrast-overlay {
                    background: linear-gradient(90deg, rgba(0,0,0,0.85) 0%, rgba(0,0,0,0.4) 50%, rgba(0,0,0,0.7) 100%);
                }
                
                .hero-main-title {
                    font-size: clamp(2.2rem, 5vw, 3.2rem);
                    letter-spacing: -0.04em;
                    line-height: 1.05;
                    max-width: 800px;
                }

                .hero-card-v4 { 
                    background: transparent;
                    aspect-ratio: 1/1;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                }
                
                .hero-card-icon-pink {
                    color: #ffffffff;
                    background: rgba(236, 72, 153, 0.1);
                    width: 54px;
                    height: 54px;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    border-radius: 50%;
                }
                
                .bg-pink-100 { background-color: #fce7f3 !important; }
                .text-pink-600 { color: #db2777 !important; }
                
                .hover-lift:hover { transform: translateY(-3px); box-shadow: 0 10px 20px rgba(0,0,0,0.2) !important; }
                .transition-all { transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); }
                
                @media (max-width: 768px) {
                    .hero-main-title { font-size: 1.6rem; }
                    .min-vh-75 { min-height: auto; padding: 2rem 0; }
                    .hero-card-v4 { aspect-ratio: auto; }
                    .hero-card-icon-pink { width: 40px; height: 40px; }
                    .hero-card-icon-pink i { font-size: 1.4rem !important; }
                    .hero-content .mb-5 { margin-bottom: 1.5rem !important; }
                    .hero-content .badge { font-size: .65rem !important; padding: .4rem .8rem !important; }
                }
            `})]})}export{d as default};
