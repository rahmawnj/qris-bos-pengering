(function (window, $) {
    'use strict';

    function normalize(value) {
        if (value === null || value === undefined) {
            return '';
        }

        return $('<div>').html(String(value)).text().replace(/\s+/g, ' ').trim();
    }

    function rowsToTable(rows, columns) {
        var header = columns.map(function (column) {
            return '<th>' + column.title + '</th>';
        }).join('');

        var body = rows.map(function (row) {
            return '<tr>' + columns.map(function (column) {
                return '<td>' + normalize(row[column.data]) + '</td>';
            }).join('') + '</tr>';
        }).join('');

        return '<table><thead><tr>' + header + '</tr></thead><tbody>' + body + '</tbody></table>';
    }

    function rowsToText(rows, columns) {
        var lines = [columns.map(function (column) {
            return column.title;
        }).join('\t')];

        rows.forEach(function (row) {
            lines.push(columns.map(function (column) {
                return normalize(row[column.data]);
            }).join('\t'));
        });

        return lines.join('\n');
    }

    function rowsToCsv(rows, columns) {
        var lines = [columns.map(function (column) {
            return column.title;
        })];

        rows.forEach(function (row) {
            lines.push(columns.map(function (column) {
                return normalize(row[column.data]);
            }));
        });

        return lines.map(function (line) {
            return line.map(function (cell) {
                return '"' + String(cell).replace(/"/g, '""') + '"';
            }).join(',');
        }).join('\r\n');
    }

    function download(content, filename, mime) {
        var blob = new Blob([content], { type: mime });
        var url = URL.createObjectURL(blob);
        var link = document.createElement('a');

        link.href = url;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
        URL.revokeObjectURL(url);
    }

    function clientRows(table, columns) {
        return table.rows({ search: 'applied' }).data().toArray().map(function (row, index) {
            var data = {};

            columns.forEach(function (column, columnIndex) {
                var sourceIndex = column.source !== undefined ? column.source : columnIndex;
                data[column.data] = column.data === 'number' ? index + 1 :
                    (typeof sourceIndex === 'function' ? sourceIndex(row, index, table) : row[sourceIndex]);
            });

            return data;
        });
    }

    function exportRows(type, rows, columns, title) {
        var filename = title.replace(/\s+/g, '-').toLowerCase();

        if (type === 'copy') {
            var text = rowsToText(rows, columns);
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(text);
            } else {
                var textarea = $('<textarea>').val(text).appendTo('body').select();
                document.execCommand('copy');
                textarea.remove();
            }
            return;
        }

        if (type === 'csv') {
            download('\ufeff' + rowsToCsv(rows, columns), filename + '.csv', 'text/csv;charset=utf-8;');
            return;
        }

        if (type === 'excel') {
            download(rowsToTable(rows, columns), filename + '.xls', 'application/vnd.ms-excel;charset=utf-8;');
            return;
        }

        if (type === 'pdf' && window.pdfMake) {
            pdfMake.createPdf({
                pageOrientation: columns.length > 5 ? 'landscape' : 'portrait',
                content: [
                    { text: title, style: 'header' },
                    {
                        table: {
                            headerRows: 1,
                            body: [
                                columns.map(function (column) { return column.title; })
                            ].concat(rows.map(function (row) {
                                return columns.map(function (column) {
                                    return normalize(row[column.data]);
                                });
                            }))
                        },
                        layout: 'lightHorizontalLines'
                    }
                ],
                styles: {
                    header: { fontSize: 14, bold: true, margin: [0, 0, 0, 10] }
                },
                defaultStyle: { fontSize: 8 }
            }).download(filename + '.pdf');
            return;
        }

        var printWindow = window.open('', '_blank');
        printWindow.document.write('<html><head><title>' + title + '</title></head><body>');
        printWindow.document.write('<h3>' + title + '</h3>');
        printWindow.document.write(rowsToTable(rows, columns));
        printWindow.document.write('</body></html>');
        printWindow.document.close();
        printWindow.focus();
        printWindow.print();
    }

    window.fullExportButtons = function (options) {
        var buttons = ['copy', 'csv', 'excel', 'pdf', 'print'];

        return buttons.map(function (type) {
            return {
                text: type === 'csv' ? 'CSV' : type.charAt(0).toUpperCase() + type.slice(1),
                className: 'btn-sm',
                action: function (e, dt) {
                    if (options.url) {
                        $.ajax({
                            url: options.url,
                            data: { search: dt.search() },
                            success: function (response) {
                                exportRows(type, response.data || [], options.columns, options.title);
                            },
                            error: options.error
                        });
                        return;
                    }

                    exportRows(type, clientRows(dt, options.columns), options.columns, options.title);
                }
            };
        });
    };
})(window, jQuery);
