import React, { useState } from 'react';
import { Link } from '@inertiajs/react';
import Modal from '@/Components/Modal';
import { formatDateTime } from '@/Utils/dateUtils';

export default function ConsultationDraftSwitcher({ drafts = [] }) {
    const [isOpen, setIsOpen] = useState(false);

    if (!drafts || drafts.length === 0) return null;

    return (
        <>
            {/* Floating Action Button */}
            <div className="fixed bottom-8 right-8 z-[60]">
                <button
                    onClick={() => setIsOpen(true)}
                    className="group relative flex h-16 w-16 items-center justify-center rounded-2xl bg-pink-600 text-white shadow-2xl transition-all duration-300 hover:scale-110 hover:bg-pink-700 active:scale-95"
                    title={`You have ${drafts.length} active sessions`}
                >
                    <div className="absolute -top-2 -right-2 flex h-6 w-6 animate-bounce items-center justify-center rounded-full bg-warning-500 text-[10px] font-black text-white shadow-sm ring-2 ring-white">
                        {drafts.length}
                    </div>
                    <i className="fas fa-history text-2xl group-hover:rotate-12 transition-transform"></i>
                    
                    {/* Ripple Effect */}
                    <span className="absolute inset-0 block rounded-2xl bg-pink-400 opacity-0 group-hover:animate-ping group-hover:opacity-20"></span>
                </button>
            </div>

            {/* Slide-over Style Modal */}
            <Modal show={isOpen} onClose={() => setIsOpen(false)} maxWidth="md">
                <div className="relative h-full bg-white shadow-2xl">
                    {/* Header */}
                    <div className="bg-gradient-to-br from-pink-500 to-pink-700 p-8 text-white relative overflow-hidden">
                        <div className="absolute -right-12 -top-12 h-40 w-40 rounded-full bg-white/10 blur-3xl"></div>
                        <div className="relative z-10 flex items-center justify-between">
                            <div>
                                <h3 className="text-2xl font-black tracking-tight flex items-center gap-3">
                                    <i className="fas fa-layer-group opacity-60"></i>
                                    Active Sessions
                                </h3>
                                <p className="mt-1 text-pink-100 text-xs font-bold uppercase tracking-widest opacity-80">
                                    In-Progress Consultations
                                </p>
                            </div>
                            <button 
                                onClick={() => setIsOpen(false)}
                                className="h-10 w-10 rounded-xl bg-white/10 flex items-center justify-center hover:bg-white/20 transition-colors"
                            >
                                <i className="fas fa-times"></i>
                            </button>
                        </div>
                    </div>

                    {/* Content */}
                    <div className="max-h-[70vh] overflow-y-auto p-6 custom-scrollbar">
                        <div className="space-y-4">
                            {drafts.map((draft) => (
                                <Link
                                    key={draft.consultation_id}
                                    href={route('consultations.edit', draft.consultation_id)}
                                    className="group block rounded-3xl border border-gray-100 bg-gray-50/50 p-5 transition-all hover:bg-white hover:shadow-xl hover:shadow-pink-100/50 hover:-translate-y-1"
                                >
                                    <div className="flex items-start justify-between">
                                        <div className="flex gap-4">
                                            <div className="flex h-12 w-12 items-center justify-center rounded-2xl bg-white font-black text-pink-600 shadow-sm transition-colors group-hover:bg-pink-600 group-hover:text-white">
                                                {draft.patient?.user?.first_name?.charAt(0) || 'P'}
                                            </div>
                                            <div>
                                                <h4 className="font-bold text-gray-900 line-clamp-1">
                                                    {draft.patient?.user?.first_name} {draft.patient?.user?.last_name}
                                                </h4>
                                                <div className="flex items-center gap-2 mt-1">
                                                    <span className="text-[10px] font-black text-gray-400 uppercase tracking-widest">
                                                        ID: PAT-{draft.patient_id}
                                                    </span>
                                                    <span className="h-1 w-1 rounded-full bg-gray-200"></span>
                                                    <span className="text-[10px] font-bold text-pink-500">
                                                        {formatDateTime(draft.updated_at || draft.consultation_date)}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {draft.chief_complaint && (
                                        <div className="mt-4 rounded-2xl bg-white border border-gray-50 p-3 italic text-xs text-gray-500 line-clamp-2">
                                            "{draft.chief_complaint}"
                                        </div>
                                    )}

                                    <div className="mt-4 flex items-center justify-end gap-2 text-[10px] font-black uppercase tracking-widest text-pink-600 opacity-0 group-hover:opacity-100 transition-opacity">
                                        Resume Assessment <i className="fas fa-chevron-right ml-1"></i>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>

                    {/* Footer */}
                    <div className="p-6 bg-gray-50/50 border-t border-gray-100 text-center">
                        <p className="text-[10px] font-bold text-gray-400 uppercase tracking-[0.2em] mb-4">
                            New session required?
                        </p>
                        <Link 
                            href={route('consultations.create')}
                            className="inline-flex items-center justify-center gap-2 w-full py-4 rounded-2xl bg-white border-2 border-dashed border-gray-200 text-gray-400 font-bold hover:border-pink-300 hover:text-pink-500 transition-all"
                        >
                            <i className="fas fa-plus-circle"></i>
                            Start Fresh Consultation
                        </Link>
                    </div>
                </div>
            </Modal>

            <style>{`
                .custom-scrollbar::-webkit-scrollbar { width: 4px; }
                .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
                .custom-scrollbar::-webkit-scrollbar-thumb { background: #fce7f3; border-radius: 10px; }
                .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #f9a8d4; }
            `}</style>
        </>
    );
}
