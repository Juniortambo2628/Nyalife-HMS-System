import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, router } from '@inertiajs/react';
import { useState, useEffect, useRef } from 'react';
import UserAvatar from '@/Components/UserAvatar';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import TableActions from '@/Components/TableActions';
import EntityReferencePicker from '@/Components/EntityReferencePicker';
import ReferenceTag from '@/Components/ReferenceTag';
import axios from 'axios';
import { formatTime } from '@/Utils/dateUtils';

export default function Index({ messages, users, filters = {}, auth }) {
    const [selectedUser, setSelectedUser] = useState(null);
    const [entities, setEntities] = useState({});
    const [selectedEntities, setSelectedEntities] = useState([]);
    const [contactSearch, setContactSearch] = useState(filters.search || '');
    const showArchived = filters.archived || false;
    const textareaRef = useRef(null);

    const applyContactSearch = (value = contactSearch) => {
        router.get(
            route('messages.index'),
            {
                search: value || undefined,
                archived: showArchived ? 1 : undefined,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const toggleArchived = () => {
        router.get(
            route('messages.index'),
            {
                search: contactSearch || undefined,
                archived: showArchived ? undefined : 1,
            },
            { preserveState: true, preserveScroll: true },
        );
    };

    const archiveConversation = () => {
        if (!selectedUser || !confirm('Archive this conversation?')) return;
        router.post(route('messages.archive', selectedUser.user_id));
    };

    const unarchiveConversation = () => {
        if (!selectedUser) return;
        router.post(route('messages.unarchive', selectedUser.user_id));
    };

    const deleteConversation = () => {
        if (!selectedUser || !confirm('Permanently delete this entire conversation?')) return;
        router.delete(route('messages.destroy-conversation', selectedUser.user_id));
    };

    const deleteMessage = (id) => {
        if (!confirm('Delete this message?')) return;
        router.delete(route('messages.destroy', id), { preserveScroll: true });
    };

    const { data, setData, post, processing, reset } = useForm({
        receiver_id: '',
        content: '',
        metadata: {
            references: [],
        },
    });

    const messagesEndRef = useRef(null);

    const scrollToBottom = () => {
        messagesEndRef.current?.scrollIntoView({ behavior: 'smooth' });
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
        axios.get(route('messages.entities')).then((res) => {
            setEntities(res.data);
        });
    }, []);

    // Shortcut detection: typing @type:: into the composer opens the picker
    // pre-filtered to that type. The shortcut list is derived from the
    // entities payload so it stays in sync with backend permissions.
    useEffect(() => {
        const shortcuts = {
            '@p::': 'patients',
            '@a::': 'appointments',
            '@c::': 'consultations',
            '@l::': 'lab_requests',
            '@m::': 'medications',
        };
        const content = data.content;
        for (const [trigger, type] of Object.entries(shortcuts)) {
            if (content.endsWith(trigger)) {
                setData((prev) => ({ ...prev, _pendingShortcut: type }));
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
            },
        });
    };

    const handleAddReference = (entity) => {
        // Prevent duplicates.
        if (selectedEntities.some((e) => e.id === entity.id && e.type === entity.type)) {
            return;
        }
        const newRefs = [...selectedEntities, { ...entity, type: entity.type }];
        setSelectedEntities(newRefs);
        setData('metadata', { references: newRefs });

        // Strip shortcut trigger if present.
        const triggers = ['@p::', '@a::', '@c::', '@l::', '@m::'];
        let newContent = data.content;
        for (const t of triggers) {
            if (newContent.endsWith(t)) {
                newContent = newContent.slice(0, -t.length);
                break;
            }
        }
        setData('content', newContent);
    };

    const removeReference = (reference) => {
        const newRefs = selectedEntities.filter((e) => !(e.id === reference.id && e.type === reference.type));
        setSelectedEntities(newRefs);
        setData('metadata', { references: newRefs });
    };

    const conversationMessages = selectedUser
        ? messages.filter((m) => m.sender_id === selectedUser.user_id || m.receiver_id === selectedUser.user_id)
        : [];

    return (
        <AuthenticatedLayout headerTitle="Direct Messages" breadcrumbs={[{ label: 'Messages', active: true }]}>
            <Head title="Messages" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'NEW MESSAGE',
                        icon: 'fa-paper-plane',
                        onClick: () => {
                            setSelectedUser(null);
                            if (textareaRef.current) textareaRef.current.focus();
                        },
                        color: 'primary',
                    },
                    {
                        label: showArchived ? 'VIEW INBOX' : 'VIEW ARCHIVED',
                        icon: showArchived ? 'fa-inbox' : 'fa-archive',
                        onClick: toggleArchived,
                        color: showArchived ? 'warning' : 'gray',
                    },
                ]}
            />

            <div className="py-0">
                <div
                    className="bg-white rounded-2xl shadow-sm overflow-hidden flex"
                    style={{ height: 'calc(100vh - 250px)' }}
                >
                    {/* Contacts List */}
                    <div className="w-1/3 border-r border-gray-100 flex flex-col">
                        <div className="p-4 border-b border-gray-100 space-y-2">
                            <div className="relative">
                                <i className="fas fa-search absolute left-4 top-1/2 -translate-y-1/2 text-gray-400"></i>
                                <input
                                    type="text"
                                    placeholder="Search people..."
                                    value={contactSearch}
                                    onChange={(e) => setContactSearch(e.targetvalue ?? e.target.value)}
                                    onKeyDown={(e) => e.key === 'Enter' && applyContactSearch()}
                                    className="w-full pl-10 pr-4 py-2 bg-gray-50 border-0 rounded-full text-sm focus:ring-2 focus:ring-pink-500"
                                />
                            </div>
                            <div className="flex gap-2">
                                <button
                                    type="button"
                                    onClick={() => applyContactSearch()}
                                    className="btn btn-sm btn-outline-secondary rounded-pill flex-1"
                                >
                                    Search
                                </button>
                                <button
                                    type="button"
                                    onClick={toggleArchived}
                                    className={`btn btn-sm rounded-pill flex-1 ${showArchived ? 'btn-warning' : 'btn-outline-secondary'}`}
                                >
                                    <i className={`fas ${showArchived ? 'fa-inbox' : 'fa-archive'} me-1`}></i>
                                    {showArchived ? 'Inbox' : 'Archived'}
                                </button>
                            </div>
                        </div>
                        <div className="flex-1 overflow-y-auto">
                            {users.length === 0 && (
                                <div className="p-4 text-center text-muted small">
                                    {showArchived
                                        ? 'No archived conversations.'
                                        : 'No conversations yet. Search to start a new message.'}
                                </div>
                            )}
                            {users.map((u) => (
                                <button
                                    key={u.user_id}
                                    onClick={() => setSelectedUser(u)}
                                    className={`w-full p-4 flex align-items-center gap-3 hover:bg-gray-50 transition-colors relative border-0 border-bottom border-gray-100 bg-transparent text-start ${selectedUser?.user_id === u.user_id ? 'bg-pink-50 border-r-4 border-pink-500' : ''}`}
                                >
                                    <div className="flex-shrink-0" style={{ width: '40px', height: '40px' }}>
                                        <UserAvatar user={u} size="sm" />
                                    </div>
                                    <div className="text-start truncate flex-1">
                                        <div className="font-bold text-gray-900 text-sm flex justify-between items-center">
                                            <span>
                                                {u.first_name} {u.last_name}
                                            </span>
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
                                <div className="p-4 bg-white border-b border-gray-100 flex align-items-center gap-3 shadow-sm z-10 justify-between">
                                    <div className="d-flex align-items-center gap-3">
                                        <div style={{ width: '40px', height: '40px' }}>
                                            <UserAvatar user={selectedUser} size="sm" />
                                        </div>
                                        <div>
                                            <div className="font-bold text-gray-900">
                                                {selectedUser.first_name} {selectedUser.last_name}
                                            </div>
                                            <div className="text-xs text-muted">@{selectedUser.username}</div>
                                        </div>
                                    </div>
                                    <TableActions
                                        actions={[
                                            showArchived
                                                ? {
                                                      icon: 'fa-inbox',
                                                      label: 'Restore conversation',
                                                      onClick: unarchiveConversation,
                                                  }
                                                : {
                                                      icon: 'fa-archive',
                                                      label: 'Archive conversation',
                                                      onClick: archiveConversation,
                                                  },
                                            {
                                                icon: 'fa-trash',
                                                label: 'Delete conversation',
                                                color: 'danger',
                                                onClick: deleteConversation,
                                            },
                                        ]}
                                    />
                                </div>

                                {/* Messages View */}
                                <div className="flex-1 overflow-y-auto p-6 space-y-6 flex flex-col">
                                    {conversationMessages
                                        .slice()
                                        .reverse()
                                        .map((m) => {
                                            const isOwn = m.sender_id === auth.user.user_id;
                                            return (
                                                <div
                                                    key={m.id}
                                                    className={`max-w-[75%] rounded-2xl p-4 shadow-sm relative group ${
                                                        isOwn
                                                            ? 'bg-pink-700 text-white ml-auto rounded-tr-none'
                                                            : 'bg-white text-gray-800 rounded-tl-none border border-gray-100'
                                                    }`}
                                                >
                                                    {isOwn && (
                                                        <button
                                                            type="button"
                                                            onClick={() => deleteMessage(m.id)}
                                                            className="absolute -top-2 -right-2 w-6 h-6 rounded-full bg-white text-danger border shadow-sm opacity-0 group-hover:opacity-100 transition-opacity"
                                                            title="Delete message"
                                                            aria-label="Delete message"
                                                        >
                                                            <i className="fas fa-times text-xs"></i>
                                                        </button>
                                                    )}
                                                    <p className="mb-2 text-sm leading-relaxed">{m.content}</p>

                                                    {/* References Rendering */}
                                                    {m.metadata?.references?.length > 0 && (
                                                        <div className="mt-2 space-y-2 border-t pt-2 border-white/20">
                                                            {m.metadata.references.map((ref, idx) => (
                                                                <ReferenceTag
                                                                    key={`${m.id}-${idx}-${ref.type}-${ref.id}`}
                                                                    reference={ref}
                                                                    variant="message"
                                                                    isOwnMessage={isOwn}
                                                                />
                                                            ))}
                                                        </div>
                                                    )}

                                                    <small
                                                        className={`text-[10px] block mt-1 ${
                                                            isOwn ? 'text-pink-200 text-end' : 'text-gray-400'
                                                        }`}
                                                    >
                                                        {formatTime(m.created_at)}
                                                    </small>
                                                </div>
                                            );
                                        })}
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
                                            {selectedEntities.map((ent) => (
                                                <ReferenceTag
                                                    key={`${ent.type}-${ent.id}`}
                                                    reference={ent}
                                                    variant="input"
                                                    onRemove={removeReference}
                                                />
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
                                                className="w-full border-0 bg-gray-50 rounded-2xl px-5 py-3 pr-14 focus:ring-2 focus:ring-pink-500 resize-none min-h-[48px]"
                                                disabled={processing}
                                                onKeyDown={(e) => {
                                                    if (e.key === 'Enter' && !e.shiftKey) {
                                                        e.preventDefault();
                                                        sendMessage(e);
                                                    }
                                                }}
                                            />
                                            <div className="absolute right-2 bottom-2">
                                                <EntityReferencePicker
                                                    entities={entities}
                                                    onSelect={handleAddReference}
                                                />
                                            </div>
                                        </div>
                                        <button
                                            type="submit"
                                            aria-label="Send message"
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
                                <p className="max-w-xs mx-auto text-gray-500">
                                    Pick a colleague or patient to start a secure conversation. You can reference
                                    clinical records using the plus button.
                                </p>
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
