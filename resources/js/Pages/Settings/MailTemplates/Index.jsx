import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router } from '@inertiajs/react';
import UnifiedToolbar from '@/Components/UnifiedToolbar';
import RegistryTablePanel from '@/Components/RegistryTablePanel';
import TableActions from '@/Components/TableActions';
import { TableCellPrimary } from '@/Components/TableCells';

export default function Index({ templates }) {
    const columns = [
        {
            header: 'Mailable Class',
            accessorKey: 'mailable',
            cell: info => <TableCellPrimary>{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Subject',
            accessorKey: 'subject',
            cell: info => <TableCellPrimary className="text-muted">{info.getValue()}</TableCellPrimary>,
        },
        {
            header: 'Actions',
            id: 'actions',
            cell: info => (
                <TableActions actions={[
                    {
                        icon: 'fa-edit',
                        label: 'Edit template',
                        href: route('mail-templates.edit', info.row.original.id),
                    },
                ]} />
            ),
        },
    ];

    return (
        <AuthenticatedLayout
            headerTitle="Email Templates"
            breadcrumbs={[
                { label: 'Settings', url: '/dashboard' },
                { label: 'Email Templates', active: true },
            ]}
        >
            <Head title="Email Templates" />

            <UnifiedToolbar
                actions={[
                    {
                        label: 'REFRESH TEMPLATES',
                        icon: 'fa-sync-alt',
                        onClick: () => router.reload({ preserveScroll: true }),
                    },
                ]}
            />

            <RegistryTablePanel
                title="Mail templates"
                icon="fa-envelope-open-text"
                data={templates}
                columns={columns}
                emptyMessage="No templates found. Run the seeder or create new ones."
            />
        </AuthenticatedLayout>
    );
}
