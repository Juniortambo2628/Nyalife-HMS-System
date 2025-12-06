import jsPDF from 'jspdf';
import 'jspdf-autotable';
import { format } from 'date-fns';

/**
 * Generate a PDF report from a table
 * @param {string} title - Report title
 * @param {Array} headers - Table headers
 * @param {Array} data - Table data (rows)
 * @param {string} filename - Output filename
 */
export function generateTablePDF(title, headers, data, filename = 'report.pdf') {
    const doc = new jsPDF();
    
    // Add title
    doc.setFontSize(18);
    doc.text(title, 14, 22);
    
    // Add date
    doc.setFontSize(11);
    doc.setTextColor(100);
    doc.text(`Generated on: ${format(new Date(), 'PPP p')}`, 14, 30);
    
    // Add table
    doc.autoTable({
        head: [headers],
        body: data,
        startY: 40,
        theme: 'grid',
        headStyles: { fillColor: [32, 201, 151] }, // Primary color
        styles: { fontSize: 10, cellPadding: 3 },
    });
    
    // Save
    doc.save(filename);
}

/**
 * Export an HTML table to PDF
 * @param {string} tableId - ID of the table element
 * @param {string} title - Report title
 * @param {string} filename - Output filename
 */
export function exportTableToPDF(tableId, title, filename) {
    const doc = new jsPDF();
    
    doc.text(title, 14, 20);
    
    doc.autoTable({ 
        html: `#${tableId}`,
        startY: 30,
        theme: 'striped'
    });
    
    doc.save(filename || `${title.toLowerCase().replace(/\s+/g, '-')}.pdf`);
}
