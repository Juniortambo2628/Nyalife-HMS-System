import{r as o,c as S,j as e,u as C,R as p,H as L,L as t}from"./app-CpzXTIU_.js";import F from"./HeroSection-CFhZC57M.js";import I from"./AppointmentSection-Cl7WGn8-.js";import R from"./AboutSection-jBPYivzZ.js";import _ from"./ServicesSection-C2M3JbvJ.js";import D from"./BlogSection-DBIJ4Qsx.js";import P from"./ContactSection-BMR2EfV-.js";function z(){const[a,m]=o.useState([]),[h,l]=o.useState(!0);if(o.useEffect(()=>{S.get("/api/insurances").then(s=>{m(s.data),l(!1)}).catch(s=>{console.error("Error fetching insurances:",s),l(!1)})},[]),h||a.length===0)return null;const n=[...a,...a,...a];return e.jsxs("section",{className:"py-5 bg-white overflow-hidden border-top",children:[e.jsx("div",{className:"container mb-4 text-center",children:e.jsx("span",{className:"text-muted small fw-bold text-uppercase tracking-wider",children:"Accepted Health Insurances"})}),e.jsx("div",{className:"insurance-slider",children:e.jsx("div",{className:"insurance-track",children:n.map((s,c)=>e.jsx("div",{className:"insurance-item flex-shrink-0 px-4",children:e.jsx("img",{src:s.logo_url,alt:s.name,className:"img-fluid grayscale-hover transition-all",style:{maxHeight:"50px",width:"auto",objectFit:"contain"}})},`ins-${c}`))})}),e.jsx("style",{children:`
                .insurance-slider {
                    position: relative;
                    width: 100%;
                    overflow: hidden;
                    padding: 20px 0;
                }
                .insurance-track {
                    display: flex;
                    width: max-content;
                    animation: scroll 40s linear infinite;
                }
                .insurance-track:hover {
                    animation-play-state: paused;
                }
                @keyframes scroll {
                    0% { transform: translateX(0); }
                    100% { transform: translateX(-50%); }
                }
                .grayscale-hover {
                    filter: grayscale(100%);
                    opacity: 0.6;
                }
                .grayscale-hover:hover {
                    filter: grayscale(0%);
                    opacity: 1;
                    transform: scale(1.1);
                }
                .tracking-wider { letter-spacing: 0.15em; }
            `})]})}function T({auth:a,laravelVersion:m,phpVersion:h,blogs:l=[],cms:n={},serviceTabs:s=[]}){const c=(n.landing_page_order||"hero,appointment,about,services,blog,contact").split(","),b=l.slice(0,3);o.useEffect(()=>(document.body.classList.add("landing"),()=>document.body.classList.remove("landing")),[]);const{data:f,setData:g,post:u,processing:j,errors:v,reset:w}=C({name:"",email:"",phone:"",date:"",time:"",reason:""}),[y,x]=p.useState(!1),[r,N]=p.useState(null),k=d=>{d.preventDefault();const i={...f};u(route("appointments.guest.store"),{onSuccess:()=>{w(),N(i),x(!0)}})};return e.jsxs(e.Fragment,{children:[e.jsx(L,{title:"Nyalife Women's Clinic - Specialized O&G Care"}),e.jsx("nav",{className:"navbar navbar-expand-lg border-bottom border-white border-opacity-10 sticky-top transition-all py-3 shadow-lg",style:{backgroundColor:"#d7056aff",backdropFilter:"blur(10px)",zIndex:1e3},children:e.jsxs("div",{className:"container d-flex align-items-center justify-content-between",children:[e.jsxs(t,{className:"navbar-brand d-flex align-items-center m-0",href:"/",children:[e.jsx("img",{src:"/assets/img/logo/Logo2-transparent.png",alt:"Nyalife HMS",height:"42",className:"me-2 bg-white rounded-2 p-1"}),e.jsxs("span",{className:"fw-bold fs-4 text-white tracking-tight",children:["Womens ",e.jsx("span",{className:"fw-light",children:"Health Clinic"})]})]}),e.jsx("button",{className:"navbar-toggler border-0 shadow-none text-white ms-auto me-2",type:"button","data-bs-toggle":"collapse","data-bs-target":"#navbarNav",children:e.jsx("i",{className:"fas fa-bars"})}),e.jsx("div",{className:"collapse navbar-collapse justify-content-center",id:"navbarNav",children:e.jsxs("ul",{className:"navbar-nav mx-auto mb-2 mb-lg-0 align-items-center gap-1",children:[e.jsx("li",{className:"nav-item",children:e.jsx("a",{className:"nav-link px-3 text-white fw-bold header-nav-link",href:"/",children:"Home"})}),e.jsx("li",{className:"nav-item",children:e.jsx("a",{className:"nav-link px-3 text-white fw-bold header-nav-link",href:"#about",children:"About"})}),e.jsx("li",{className:"nav-item",children:e.jsx("a",{className:"nav-link px-3 text-white fw-bold header-nav-link",href:"#services",children:"Services"})}),e.jsx("li",{className:"nav-item",children:e.jsx("a",{className:"nav-link px-3 text-white fw-bold header-nav-link",href:"#blog",children:"Journal"})}),e.jsx("li",{className:"nav-item",children:e.jsx("a",{className:"nav-link px-3 text-white fw-bold header-nav-link",href:"#contact",children:"Contact"})}),e.jsx("li",{className:"nav-item d-lg-none mt-3 border-top border-white border-opacity-25 pt-3 w-100",children:e.jsx("div",{className:"d-flex flex-column gap-2 px-2 pb-3",children:a.user?e.jsxs(t,{href:route("dashboard"),className:"btn btn-outline-light btn-md rounded-pill px-4 py-2 fw-bold w-100",children:[e.jsx("i",{className:"fas fa-tachometer-alt me-2"}),"Dashboard"]}):e.jsxs(e.Fragment,{children:[e.jsxs(t,{href:route("login.patient"),className:"btn btn-outline-light rounded-pill px-4 py-2 fw-bold w-100",children:[e.jsx("i",{className:"fas fa-sign-in-alt me-2"}),"Patient Login"]}),e.jsxs(t,{href:route("login.staff"),className:"btn btn-white bg-white text-primary rounded-pill px-4 py-2 fw-bold w-100",children:[e.jsx("i",{className:"fas fa-user-md me-2"}),"Staff Portal"]})]})})})]})}),e.jsx("div",{className:"d-none d-lg-flex gap-2 align-items-center",children:a.user?e.jsx(t,{href:route("dashboard"),className:"btn btn-outline-light rounded-pill px-4 fw-bold btn-md",children:"Dashboard"}):e.jsxs(e.Fragment,{children:[e.jsx(t,{href:route("login.patient"),className:"btn btn-outline-light rounded-pill px-4 fw-bold btn-md",children:"Patient login"}),e.jsx(t,{href:route("login.staff"),className:"btn btn-outline-light rounded-pill px-4 fw-bold btn-md",children:"Staff portal"})]})})]})}),e.jsxs("div",{className:"landing-main overflow-hidden",children:[c.map(d=>{const i=d.trim();return i==="hero"?e.jsx(F,{cms:n,isLoggedIn:!!a.user},"hero"):i==="appointment"&&!a.user?e.jsx(I,{data:f,setData:g,handleSubmit:k,processing:j,errors:v},"appointment"):i==="about"?e.jsx(R,{cms:n},"about"):i==="services"?e.jsx(_,{serviceTabs:s},"services"):i==="blog"?e.jsx(D,{blogs:b},"blog"):i==="contact"?e.jsx(P,{cms:n},"contact"):null}),e.jsx(z,{})]}),y&&e.jsx("div",{className:"modal show d-block",style:{backgroundColor:"rgba(0,0,0,0.6)",zIndex:9999},children:e.jsx("div",{className:"modal-dialog modal-dialog-centered",children:e.jsx("div",{className:"modal-content border-0 rounded-4 shadow-lg p-4",children:e.jsxs("div",{className:"modal-body text-center py-5",children:[e.jsx("div",{className:"bg-success bg-opacity-10 text-success rounded-circle d-inline-flex align-items-center justify-content-center mb-4",style:{width:"80px",height:"80px"},children:e.jsx("i",{className:"fas fa-check fa-2x"})}),e.jsx("h3",{className:"fw-bold mb-3",children:"Request Received!"}),e.jsxs("p",{className:"text-muted mb-4 lead",children:["Our team will contact you shortly at ",e.jsx("strong",{children:r?.email})," to coordinate your visit."]}),e.jsxs("div",{className:"d-grid gap-2",children:[e.jsxs(t,{href:`/register?name=${encodeURIComponent(r?.name)}&email=${encodeURIComponent(r?.email)}&phone=${encodeURIComponent(r?.phone)}`,className:"btn btn-primary btn-lg rounded-pill fw-bold",children:[e.jsx("i",{className:"fas fa-user-plus me-2"}),"Complete Registration"]}),e.jsx("button",{type:"button",className:"btn btn-light btn-lg rounded-pill text-muted fw-bold",onClick:()=>x(!1),children:"Done"})]})]})})})}),e.jsx("footer",{className:"footer-elegant pt-5 text-white overflow-hidden",children:e.jsxs("div",{className:"container py-3",children:[e.jsxs("div",{className:"row g-5 mb-5",children:[e.jsxs("div",{className:"col-lg-4 pe-lg-5",children:[e.jsx("img",{src:"/assets/img/logo/Logo2-transparent.png",alt:"Logo",height:"50",className:"mb-4 bg-white rounded p-1"}),e.jsx("p",{className:"opacity-75 mb-4 footer-text",children:"Providing exceptional obstetrics and gynecology services. Our commitment is to offer compassionate, evidence-based care tailored to your unique journey."}),e.jsxs("div",{className:"d-flex gap-2",children:[e.jsx("a",{href:"https://www.instagram.com/nyalife_womenshealth",target:"_blank",rel:"noopener noreferrer",className:"social-link",children:e.jsx("i",{className:"fab fa-instagram fa-sm"})}),e.jsx("a",{href:"https://www.linkedin.com/company/nyalife-women-s-health/",target:"_blank",rel:"noopener noreferrer",className:"social-link",children:e.jsx("i",{className:"fab fa-linkedin-in fa-sm"})})]})]}),e.jsxs("div",{className:"col-lg-2 col-md-4",children:[e.jsx("h6",{className:"fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block",children:"Services"}),e.jsxs("ul",{className:"list-unstyled footer-links",children:[e.jsx("li",{children:e.jsx("a",{href:"#services",children:"Prenatal care"})}),e.jsx("li",{children:e.jsx("a",{href:"#services",children:"Gynecology"})}),e.jsx("li",{children:e.jsx("a",{href:"#services",children:"Family planning"})}),e.jsx("li",{children:e.jsx("a",{href:"#services",children:"Fertility support"})})]})]}),e.jsxs("div",{className:"col-lg-2 col-md-4",children:[e.jsx("h6",{className:"fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block",children:"Portal"}),e.jsxs("ul",{className:"list-unstyled footer-links",children:[e.jsx("li",{children:e.jsx(t,{href:route("login.patient"),children:"Patient login"})}),e.jsx("li",{children:e.jsx(t,{href:route("login.staff"),children:"Staff login"})}),e.jsx("li",{children:e.jsx(t,{href:route("privacy-policy"),children:"Privacy policy"})}),e.jsx("li",{children:e.jsx(t,{href:route("terms-of-service"),children:"Terms of service"})})]})]}),e.jsxs("div",{className:"col-lg-4 col-md-4",children:[e.jsx("h6",{className:"fw-bold mb-4 border-bottom border-white border-opacity-10 pb-2 d-inline-block",children:"Contact"}),e.jsxs("div",{className:"contact-info footer-text",children:[e.jsxs("div",{className:"mb-4 mt-4",children:[e.jsx("h6",{className:"fw-bold opacity-50 mb-1 border-bottom border-white border-opacity-70 pb-2 d-inline-block",children:"Email"}),e.jsx("p",{className:"mb-1 footer-text",children:"info@nyalifewomensclinic.net"}),e.jsx("p",{className:"mb-0 footer-text",children:"nyalifewomenshealth@gmail.com"})]}),e.jsxs("div",{className:"mb-4 mt-4",children:[e.jsx("h6",{className:"fw-bold opacity-50 mb-1 border-bottom border-white border-opacity-70 pb-2 d-inline-block",children:"Phone"}),e.jsx("p",{className:"mb-0 footer-text",children:"0746 516514"})]}),e.jsxs("div",{className:"mb-4 mt-4",children:[e.jsx("h6",{className:"fw-bold opacity-50 mb-2 border-bottom border-white border-opacity-90 pb-2 d-inline-block",children:"Location"}),e.jsx("p",{className:"mb-0 footer-text",children:"A104, Mlolongo along Mombasa road sidelane at Jempark office complex building, Athi River, Kenya"})]})]})]})]}),e.jsxs("div",{className:"py-4 px-4 d-flex flex-column flex-md-row justify-content-between align-items-center",children:[e.jsxs("p",{className:"opacity-50 mb-0 footer-text",children:["© ",new Date().getFullYear()," Nyalife Women's Clinic. All rights reserved."]}),e.jsxs("a",{href:"https://www.okjtech.co.ke",target:"_blank",className:"d-flex align-items-center gap-2 text-white text-decoration-none opacity-50 hover-opacity-100 transition-all",children:[e.jsx("span",{className:"small footer-text",children:"Made with precision, care and intention by"}),e.jsx("img",{src:"/assets/img/OKJTechLogo-White_Transparent.png",alt:"OKJTech",className:"opacity-75 footer-logo-fixed",style:{height:"35px",width:"auto",display:"inline-block",objectFit:"contain"}})]})]})]})}),e.jsx("style",{children:`
                .footer-elegant { 
                    background: linear-gradient(180deg, #058b7c 0%, #036b5e 100%); 
                    position: relative;
                    font-family: inherit;
                }
                .footer-text, .footer-links a{
                    font-family: inherit;
                    color: white !important;
                    font-size: 1rem;
                    line-height: 1.5;
                }

                .footer-elegant h6 {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1.4rem;
                    font-weight: 600;
                }

                .footer-text h6 {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1.25rem;
                    font-weight: 600;
                }

                .footer-text p {
                    font-family: inherit;
                    color: white !important;
                    font-size: 1rem;
                    line-height: 1.5;
                }

                .footer-links li { margin-bottom: 0.3rem; }
                .footer-links a { opacity: 1; text-decoration: none; font-size: .9rem; transition: all 0.2s; }
                .footer-links a:hover { opacity: 1; transform: translateX(3px); }
                .social-link { width: 42px; height: 42px; background: rgba(255,255,255,0.1); color: #fff !important; display: flex; align-items: center; justify-content: center; border-radius: 50%; transition: all 0.3s; }
                .social-link:hover { background: #fff; color: #058b7c !important; transform: scale(1.1); }
                
                .header-nav-link {
                    color: #ffffff !important;
                    display: block !important;
                    visibility: visible !important;
                    text-shadow: 0 1px 2px rgba(0,0,0,0.1);
                }
                .footer-logo-fixed {
                    height: 34px !important;
                    width: auto !important;
                    max-width: 100px !important;
                }
                .hover-translate-y:hover { transform: translateY(-2px); }
                .transition-all { transition: all 0.3s ease-out; }
            `})]})}export{T as default};
