/**
 * exportTableToCSV - Exports the visible table on the page to a CSV file
 * @param {string} filename - The base name for the downloaded file
 */
function exportTableToCSV(filename) {
    const table = document.querySelector('table');
    if (!table) {
        alert('No table found to export.');
        return;
    }

    const rows = table.querySelectorAll('tr');
    const csvData = [];

    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        const rowData = [];
        cols.forEach(col => {
            // Get text content, clean it, and escape quotes
            let text = col.textContent.trim().replace(/\s+/g, ' ');
            // Escape double quotes by doubling them
            text = text.replace(/"/g, '""');
            rowData.push(`"${text}"`);
        });
        csvData.push(rowData.join(','));
    });

    const csvContent = csvData.join('\n');
    const blob = new Blob(['\uFEFF' + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);

    const now = new Date();
    const timestamp = now.getFullYear() + '-' +
        String(now.getMonth() + 1).padStart(2, '0') + '-' +
        String(now.getDate()).padStart(2, '0') + '_' +
        String(now.getHours()).padStart(2, '0') + '-' +
        String(now.getMinutes()).padStart(2, '0');

    const link = document.createElement('a');
    link.href = url;
    link.download = `${filename}_${timestamp}.csv`;
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

// Auto-wire export buttons when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    // Wire all export buttons by looking for common selectors
    const exportButtons = document.querySelectorAll('.export-sales-data-container button, .export-btn, .export-audit-log-button');
    exportButtons.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            // Determine filename from page title or data attribute
            const pageName = btn.dataset.exportName || document.title.replace(/[^a-zA-Z0-9]/g, '_') || 'export';
            exportTableToCSV(pageName);
        });
    });
});
