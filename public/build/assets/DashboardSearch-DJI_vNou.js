import{r as i,j as e}from"./app-Da5OSHUk.js";function f({placeholder:d="Search anything...",value:a="",onChange:s,onSubmit:o,className:c=""}){const[t,n]=i.useState(a);i.useEffect(()=>{n(a)},[a]);const p=r=>{const l=r.target.value;n(l),s&&s(l)},m=r=>{r.preventDefault(),o&&o(t)};return e.jsxs("div",{className:`dashboard-search-container mb-4 ${c}`,children:[e.jsx("form",{onSubmit:m,className:"card border-0 shadow-sm rounded-2xl bg-white p-1 shadow-hover transition-all duration-300",style:{overflow:"visible"},children:e.jsxs("div",{className:"input-group input-group-lg",children:[e.jsx("span",{className:"input-group-text bg-transparent border-0 ps-4 pe-2 py-3",children:e.jsx("i",{className:"fas fa-search text-gray-300 fs-4"})}),e.jsx("input",{type:"text",className:"form-control border-0 bg-transparent fs-5 ps-2 py-3 shadow-none no-focus-outline",placeholder:d,value:t,onChange:p,style:{height:"60px"}}),e.jsx("div",{className:"p-2 d-flex align-items-center",children:e.jsxs("button",{type:"submit",className:"btn btn-primary rounded-xl px-5 h-100 font-bold shadow-sm d-flex align-items-center gap-3 transition-all hover-scale",style:{minWidth:"160px"},children:[e.jsx("span",{className:"fs-5",children:"Search"}),e.jsx("i",{className:"fas fa-arrow-right opacity-50"})]})})]})}),e.jsxs("div",{className:"d-flex flex-wrap gap-2 mt-2 px-2 overflow-auto no-scrollbar",children:[e.jsx("small",{className:"text-gray-400 font-bold uppercase tracking-wider align-middle pt-1 me-2",style:{fontSize:"0.65rem"},children:"Quick Filters:"}),e.jsx("span",{className:"badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold shadow-sm",children:"Recently Added"}),e.jsx("span",{className:"badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold shadow-sm",children:"Active Only"}),e.jsx("span",{className:"badge rounded-pill nyl-filter-badge border px-3 py-2 cursor-pointer transition-all font-semibold shadow-sm",children:"Archived"})]}),e.jsx("style",{children:`
                .nyl-filter-badge {
                    background-color: #fff;
                    color: #e91e63 !important;
                    border-color: #e91e6333 !important;
                }
                .nyl-filter-badge:hover {
                    background-color: #e91e63 !important;
                    color: #fff !important;
                    transform: translateY(-1px);
                }
                .no-focus-outline:focus { outline: none !important; box-shadow: none !important; }
                .shadow-hover:hover { box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1) !important; }
                .hover-scale { transition: transform 0.2s; }
                .hover-scale:hover { transform: scale(1.02); }
                .hover-scale:active { transform: scale(0.98); }
            `})]})}export{f as D};
