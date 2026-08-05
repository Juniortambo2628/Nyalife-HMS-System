import React from 'react';
import { Head, Link } from '@inertiajs/react';

export default function MeetingRoom({ meetingId, jitsiDomain, user }) {
    const roomName = meetingId;
    const jitsiUrl = `https://${jitsiDomain}/${roomName}#config.startWithAudioMuted=true&config.prejoinPageEnabled=false&interfaceConfig.DISABLE_JOIN_LEAVE_NOTIFICATIONS=true&interfaceConfig.SHOW_CHROME_EXTENSION_BANNER=false`;

    return (
        <div className="vh-100 d-flex flex-column bg-dark">
            <Head title={`Telehealth Meeting - ${meetingId}`} />

            <nav
                className="navbar navbar-dark bg-dark border-bottom border-secondary px-3 py-1"
                style={{ minHeight: 48 }}
            >
                <div className="container-fluid">
                    <Link className="navbar-brand d-flex align-items-center gap-2 py-0" href="/">
                        <img src="/assets/img/logo/Logo2-transparent.png" alt="Nyalife" height="30" />
                        <span className="fw-bold fs-6 text-white">Nyalife Telehealth</span>
                    </Link>
                    <div className="d-flex align-items-center gap-3">
                        {user && (
                            <span className="text-light small">
                                {user.first_name} {user.last_name}
                            </span>
                        )}
                        <a
                            href={`https://${jitsiDomain}/${roomName}`}
                            target="_blank"
                            rel="noopener noreferrer"
                            className="btn btn-outline-light btn-sm rounded-pill"
                        >
                            <i className="fas fa-external-link-alt me-1"></i> Open in New Tab
                        </a>
                    </div>
                </div>
            </nav>

            <div className="flex-grow-1 position-relative">
                <iframe
                    src={jitsiUrl}
                    allow="camera; microphone; fullscreen; display-capture; autoplay"
                    className="w-100 h-100 border-0"
                    style={{ position: 'absolute', top: 0, left: 0 }}
                    title="Jitsi Telehealth Meeting"
                />
            </div>
        </div>
    );
}
