import React, { useEffect, useState } from 'react';
import axios from 'axios';

export default function InsuranceCarousel() {
    const [insurances, setInsurances] = useState([]);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        axios.get('/api/insurances')
            .then(response => {
                setInsurances(response.data);
                setLoading(false);
            })
            .catch(error => {
                console.error('Error fetching insurances:', error);
                setLoading(false);
            });
    }, []);

    if (loading || insurances.length === 0) return null;

    // Double the items for seamless loop
    const displayItems = [...insurances, ...insurances, ...insurances];

    return (
        <section className="py-5 bg-white overflow-hidden border-top">
            <div className="container mb-4 text-center">
                <span className="text-muted small fw-bold text-uppercase tracking-wider">Accepted Health Insurances</span>
            </div>
            
            <div className="insurance-slider">
                <div className="insurance-track">
                    {displayItems.map((item, idx) => (
                        <div key={`ins-${idx}`} className="insurance-item flex-shrink-0 px-4">
                            <img 
                                src={item.logo_url} 
                                alt={item.name} 
                                className="img-fluid grayscale-hover transition-all"
                                style={{ maxHeight: '50px', width: 'auto', objectFit: 'contain' }}
                            />
                        </div>
                    ))}
                </div>
            </div>

            <style>{`
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
            `}</style>
        </section>
    );
}
