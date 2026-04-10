import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import PageHeader from '@/Components/PageHeader';
import axios from 'axios';

export default function Index({ messages, users, auth }) {
    const [selectedUser, setSelectedUser] = useState(null);
    const [entities, setEntities] = useState({});
    const [showEntitySelector, setShowEntitySelector] = useState(false);
    const [selectedEntities, setSelectedEntities] = useState([]);
    
    // New Search & Filter states
    const [entitySearch, setEntitySearch] = useState('');
    const [activeFilter, setActiveFilter] = useState('all');
    
    const { data, setData, post, processing, reset } = useForm({
        receiver_id: '',
        content: '',
        metadata: {
            references: []
        }
    });

    const messagesEndRef = useRef(null);
    const textareaRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: "smooth" });
    };

    useEffect(() => {
        scrollToBottom();
    }, [messages, selectedUser]);

    useEffect(() => {
        if (selectedUser) {
            setData('receiver_id', selectedUser.user_id);
            // Mark as read
            if (selectedUser.unread_count > 0) {
                router.post(route('messages.mark-all-read', selectedUser.user_id), {}, { preserveScroll: true });
            }
        }
    }, [selectedUser]);

    useEffect(() => {
        // Fetch referenceable entities
        axios.get(route('messages.entities')).then(res => {
            setEntities(res.data);
        });
    }, []);

    // Shortcut detection
    useEffect(() => {
        const content = data.content;
        const shortcuts = {
            '@p::': 'patients',
            '@a::': 'appointments',
            '@c::': 'consultations',
            '@l::': 'lab_requests',
            '@m::': 'medications'
        };

        for (const [trigger, type] of Object.entries(shortcuts)) {
            if (content.endsWith(trigger)) {
                setActiveFilter(type);
                setShowEntitySelector(true);
                // Highlight trigger visually? or just open selector
                break;
            }
        }
    }, [data.content]);

    const sendMessage = (e) => {
        e.preventDefault();
        post(route('messages.store'), {
            onSuccess: () => {
                reset('content');
                setSelectedEntities([]);
                setData('metadata', { references: [] });
            }
        });
    };

    const addReference = (entity) => {
        const newRefs = [...selectedEntities, entity];
        setSelectedEntities(newRefs);
        setData('metadata', { references: newRefs });
        setShowEntitySelector(false);
        setEntitySearch('');
        
        // Clean up shortcut trigger if present
        const triggers = ['@p::', '@a::', '@c::', '@l::', '@m::'];
        let newContent = data.content;
        triggers.forEach(t => {
            if (newContent.endsWith(t)) {
                newContent = newContent.slice(0, -t.length);
            }
        });
        setData('content', newContent);
    };

    const removeReference = (id) => {
        const newRefs = selectedEntities.filter(e => e.id !== id);
        setSelectedEntities(newRefs);
        setData('metadata', { references: newRefs });
    };

    const conversationMessages = selectedUser 
        ? messages.filter(m => m.sender_id === selectedUser.user_id || m.receiver_id === selectedUser.user_id)
        : [];

    // Filtered Entities Logic
    const getFilteredEntities = () => {
        let result = {};
        Object.entries(entities).forEach(([type, list]) => {
            if (activeFilter !== 'all' && activeFilter !== type) return;
            
            const filtered = list.filter(item => 
                item.label.toLowerCase().includes(entitySearch.toLowerCase())
            );
            
            if (filtered.length > 0) {
                result[type] = filtered;
            }
        });
        return result;
    };

    const filteredEntities = getFilteredEntities();

    return (
        <AuthenticatedLayout
            header="Messages"
        >
            <Head title="Messages" />

            <PageHeader 
                title="Direct Messages"
                breadcrumbs={[{ label: 'Messages', active: true }]}
            />

            <div className="py-0">
                <div className="bg-white rounded-2xl shadow-sm overflow-hidden flex" style={{ height: 'calc(100vh - 250px)' }}>
                    {/* Contacts List */}
                    <div className="w-1/3 border-r border-gray-100 flex flex-col">
                        <div className="p-4 border-b border-gray-100">
                            <div className="relative">
                                <i className="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input 
                                    type="text" 
                                    placeholder="Search people..." 
                                    className="w-full pl-10 pr-4 py-2 bg-gray-50 border-0 rounded-full text-sm focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            {users.map(u => (
                                <button 
                                    key={u.user_id} 
                                    onClick={() => setSelectedUser(u)}
                                    className={`w-full p-4 flex align-items-center gap-3 hover:bg-gray-50 transition-colors relative ${selectedUser?.user_id === u.user_id ? 'bg-pink-50 border-r-4 border-pink-500' : ''}`}
                                >
                                    <div className="avatar-circle flex-shrink-0" style={{ width: '40px', height: '40px', borderRadius: '50%', background: 'linear-gradient(135deg, #e91e63, #c2185b)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontWeight: 'bold', fontSize: '14px' }}>
                                        {u.first_name.charAt(0)}
                                    </div>
                                    <div className="text-start truncate flex-1">
                                        <div className="font-bold text-gray-900 text-sm flex justify-between items-center">
                                            <span>{u.first_name} {u.last_name}</span>
                                            {u.unread_count > 0 && (
                                                <span className="bg-danger text-white text-[10px] px-1.5 py-0.5 rounded-full min-w-[18px] text-center">
                                                    {u.unread_count}
                                                </span>
                                            )}
                                        </div>
                                        <div className="text-xs text-gray-500 truncate">@{u.username}</div>
                                    </div>
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Chat Area */}
                    <div className="flex-1 flex flex-col bg-gray-50/30">
                        {selectedUser ? (
                            <>
                                {/* Header */}
                                <div className="p-4 bg-white border-b border-gray-100 flex align-items-center gap-3 shadow-sm z-10">
                                    <div className="avatar-circle" style={{ width: '40px', height: '40px', borderRadius: '50%', background: 'linear-gradient(135deg, #e91e63, #c2185b)', display: 'flex', alignItems: 'center', justifyContent: 'center', color: 'white', fontWeight: 'bold' }}>
                                        {selectedUser.first_name.charAt(0)}
                                    </div>
                                    <div>
                                        <div className="font-bold text-gray-900">{selectedUser.first_name} {selectedUser.last_name}</div>
                                        <div className="text-xs text-success">Online</div>
                                    </div>
                                </div>

                                {/* Messages View */}
                                <div className="flex-1 overflow-y-auto p-6 space-y-6 flex flex-col">
                                    {conversationMessages.slice().reverse().map(m => (
                                        <div key={m.id} className={`max-w-[75%] rounded-2xl p-4 shadow-sm relative ${m.sender_id === auth.user.user_id ? 'bg-pink-600 text-white ml-auto rounded-tr-none' : 'bg-white text-gray-800 rounded-tl-none'}`}>
                                            <p className="mb-2 text-sm leading-relaxed">{m.content}</p>
                                            
                                            {/* References Rendering */}
                                            {m.metadata?.references?.length > 0 && (
                                                <div className="mt-2 space-y-2 border-t pt-2 border-white/20">
                                                    {m.metadata.references.map((ref, idx) => (
                                                        <div key={idx} className={`text-xs p-2 rounded-xl flex items-center gap-2 ${m.sender_id === auth.user.user_id ? 'bg-white/10 text-white' : 'bg-gray-50 text-gray-700 border border-gray-100'}`}>
                                                            <i className={`fas ${
                                                                ref.type === 'patient' ? 'fa-user-injured' : 
                                                                ref.type === 'appointment' ? 'fa-calendar-check' :
                                                                ref.type === 'consultation' ? 'fa-stethoscope' :
                                                                ref.type === 'lab_request' ? 'fa-vial' : 'fa-pills'
                                                            } opacity-60`}></i>
                                                            <span className="font-medium truncate">{ref.label}</span>
                                                        </div>
                                                    ))}
                                                </div>
                                            )}

                                            <small className={`text-[10px] block mt-1 opacity-70 ${m.sender_id === auth.user.user_id ? 'text-end' : ''}`}>
                                                {new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}
                                            </small>
                                        </div>
                                    ))}
                                    <div ref={messagesEndRef} />
                                    {conversationMessages.length === 0 && (
                                        <div className="h-full flex flex-col items-center justify-center opacity-30 text-center py-20">
                                            <i className="fas fa-comments text-6xl mb-4"></i>
                                            <p className="font-bold">No messages yet with {selectedUser.first_name}.</p>
                                            <p className="text-sm">Start the conversation below!</p>
                                        </div>
                                    )}
                                </div>

                                {/* Input */}
                                <div className="p-4 bg-white border-t border-gray-100 shadow-[0_-4px_10px_rgba(0,0,0,0.02)]">
                                    {/* Selected References Tags */}
                                    {selectedEntities.length > 0 && (
                                        <div className="flex flex-wrap gap-2 mb-3">
                                            {selectedEntities.map(ent => (
                                                <span key={ent.id} className="bg-pink-100 text-pink-600 text-xs px-3 py-1.5 rounded-full flex items-center gap-2 font-medium">
                                                    <i className="fas fa-link opacity-60"></i>
                                                    {ent.label}
                                                    <button onClick={() => removeReference(ent.id)} className="hover:text-pink-800"><i className="fas fa-times"></i></button>
                                                </span>
                                            ))}
                                        </div>
                                    )}

                                    <form onSubmit={sendMessage} className="flex gap-3 items-end">
                                        <div className="flex-1 relative">
                                            <textarea 
                                                ref={textareaRef}
                                                rows="1"
                                                value={data.content}
                                                onChange={(e) => setData('content', e.target.value)}
                                                placeholder="Type your message... (Try @p:: for patients)" 
                                                className="w-full border-0 bg-gray-50 rounded-2xl px-5 py-3 pr-12 focus:ring-2 focus:ring-pink-500 resize-none min-h-[48px]"
                                                disabled={processing}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' && !e.shiftKey) {
                                                        e.preventDefault();
                                                        sendMessage(e);
                                                    }
                                                }}
                                            />
                                            <button 
                                                type="button" 
                                                onClick={() => setShowEntitySelector(!showEntitySelector)}
                                                className={`absolute right-3 bottom-2.5 w-8 h-8 rounded-full flex items-center justify-center transition-colors ${showEntitySelector ? 'bg-pink-500 text-white' : 'text-gray-400 hover:bg-gray-200'}`}
                                            >
                                                <i className="fas fa-plus"></i>
                                            </button>

                                            {/* Advanced Entity Selector Dropdown */}
                                            {showEntitySelector && (
                                                <div className="absolute bottom-full right-0 mb-4 w-80 max-h-[500px] bg-white shadow-2xl rounded-2xl border border-gray-100 overflow-hidden flex flex-col z-50">
                                                    <div className="p-3 bg-gray-50 border-b border-gray-100">
                                                        <div className="font-bold text-xs text-gray-500 uppercase tracking-wider mb-2">Reference Hospital Records</div>
                                                        <div className="relative mb-2">
                                                            <i className="fas fa-search absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-xs"></i>
                                                            <input 
                                                                type="text" 
                                                                autoFocus
                                                                value={entitySearch}
                                                                onChange={(e) => setEntitySearch(e.target.value)}
                                                                placeholder="Search entities..."
                                                                className="w-full pl-8 pr-3 py-1.5 bg-white border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-pink-500"
                                                            />
                                                        </div>
                                                        <div className="flex gap-1 overflow-x-auto pb-1 no-scrollbar">
                                                            <button 
                                                                type="button"
                                                                onClick={() => setActiveFilter('all')}
                                                                className={`px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap transition-colors ${activeFilter === 'all' ? 'bg-pink-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-100'}`}
                                                            >
                                                                All
                                                            </button>
                                                            {Object.keys(entities).map(type => (
                                                                <button 
                                                                    key={type}
                                                                    type="button"
                                                                    onClick={() => setActiveFilter(type)}
                                                                    className={`px-3 py-1 rounded-full text-[10px] font-bold whitespace-nowrap transition-colors uppercase ${activeFilter === type ? 'bg-pink-500 text-white' : 'bg-white text-gray-500 border border-gray-200 hover:bg-gray-100'}`}
                                                                >
                                                                    {type.replace('_', ' ')}
                                                                </button>
                                                            ))}
                                                        </div>
                                                    </div>
                                                    <div className="overflow-y-auto p-2 space-y-4">
                                                        {Object.entries(filteredEntities).length > 0 ? (
                                                            Object.entries(filteredEntities).map(([type, list]) => (
                                                                <div key={type}>
                                                                    <div className="px-2 mb-1 text-[10px] font-bold text-gray-400 uppercase">{type.replace('_', ' ')}</div>
                                                                    {list.map(ent => (
                                                                        <button 
                                                                            key={ent.id}
                                                                            type="button"
                                                                            onClick={() => addReference(ent)}
                                                                            className="w-full p-2 text-start text-xs hover:bg-pink-50 rounded-lg transition-colors flex items-center gap-2 group"
                                                                        >
                                                                            <i className="fas fa-plus text-pink-500 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                                                            <span className="truncate">{ent.label}</span>
                                                                        </button>
                                                                    ))}
                                                                </div>
                                                            ))
                                                        ) : (
                                                            <div className="text-center py-8 opacity-40">
                                                                <i className="fas fa-search mb-2 d-block"></i>
                                                                <div className="text-[10px] font-bold">No results found</div>
                                                            </div>
                                                        )}
                                                    </div>
                                                </div>
                                            )}
                                        </div>
                                        <button 
                                            type="submit" 
                                            disabled={processing || !data.content.trim()}
                                            className="bg-pink-600 text-white w-12 h-12 rounded-2xl flex items-center justify-center shadow-lg shadow-pink-600/20 hover:scale-105 active:scale-95 transition-all disabled:opacity-50"
                                        >
                                            <i className="fas fa-paper-plane"></i>
                                        </button>
                                    </form>
                                </div>
                            </>
                        ) : (
                            <div className="flex-1 flex flex-col items-center justify-center opacity-30 text-center">
                                <div className="w-48 h-48 bg-gray-200 rounded-full flex items-center justify-center mb-8 pulse-slow">
                                    <i className="fas fa-paper-plane text-6xl text-gray-400"></i>
                                </div>
                                <h2 className="text-2xl font-bold text-gray-900">Hospital Communications</h2>
                                <p className="max-w-xs mx-auto text-gray-500">Pick a colleague or patient to start a secure conversation. You can reference clinical records using the plus button.</p>
                            </div>
                        )}
                    </div>
                </div>
            </div>

            <style>{`
                .w-1\/3 { width: 33.333333%; }
                .flex-1 { flex: 1 1 0%; }
                .rounded-2xl { border-radius: 1.25rem; }
                .shadow-2xl { box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.15); }
                @keyframes pulse-slow {
                    0%, 100% { opacity: 0.3; transform: scale(1); }
                    50% { opacity: 0.4; transform: scale(1.05); }
                }
                .pulse-slow { animation: pulse-slow 4s infinite ease-in-out; }
                .no-scrollbar::-webkit-scrollbar { display: none; }
                .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
            `}</style>
        </AuthenticatedLayout>
    );
}
