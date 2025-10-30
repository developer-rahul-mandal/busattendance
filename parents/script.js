let currentCardData = null;
        document.getElementById('downloadBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            await generateIDCard('downloadIDCard');
        });
        document.getElementById('printBtn').addEventListener('click', async function(e) {
            e.preventDefault();
            await generateIDCard('printIDCard');
        });

        async function generateIDCard(type) {
            const formData = new FormData(document.getElementById('idCardForm'));
            const data = Object.fromEntries(formData.entries());

            if (type === 'downloadIDCard') {
                document.getElementById('downloadBtn').disabled = true;
            } else {
                document.getElementById('printBtn').disabled = true;
            }

            Swal.fire({
                title: 'Generating ID Card...',
                text: 'Please wait a few seconds.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            try {
                const response = await fetch('process_create_id_card.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify(data)
                });

                const responseText = await response.text();
                let result;

                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    throw new Error('Server returned invalid JSON. Please check the API endpoint.');
                }

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                if (result.success) {
                    currentCardData = result.data;
                    // console.log(result.data);
                    if(type === 'downloadIDCard') {
                        downloadIDCard();
                    } else {
                        printIDCard();
                    }
                    Swal.close();

                    Swal.fire({
                        icon: 'success',
                        title: 'Download Started!',
                        text: `Student ID: ${currentCardData.student_id}`,
                        timer: 2000,
                        showConfirmButton: false
                    });
                } else {
                    throw new Error(result.error || 'Failed to generate ID card');
                }

                

            } catch (error) {
                // console.error('Error:', error);
                
            } finally {
                if (type === 'downloadIDCard') {
                    document.getElementById('downloadBtn').disabled = false;
                } else {
                    document.getElementById('printBtn').disabled = false;
                }
            }
        }

        function downloadIDCard() {
            if (!currentCardData) return;

            const link = document.createElement('a');
            link.download = `student_id_${currentCardData.student_id}.png`;
            link.href = `data:image/png;base64,${currentCardData.base64_image}`;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            Swal.fire({
                icon: 'success',
                title: 'Download Started!',
                text: `Student ID: ${currentCardData.student_id}`,
                timer: 2000,
                showConfirmButton: false
            });

        }

        function printIDCard() {
            if (!currentCardData) return;

            const imageSrc = `data:image/png;base64,${currentCardData.base64_image}`;

            // Create a new print window
            const printWindow = window.open('', '_blank');
            printWindow.document.write(`
                <html>
                    <head>
                        <title>Print ID Card</title>
                        <style>
                            body {
                                text-align: center;
                                margin: 0;
                                padding: 20px;
                                background: #fff;
                            }
                            img {
                                max-width: 100%;
                                height: auto;
                                border: 2px solid #000;
                            }
                            @media print {
                                body {
                                    -webkit-print-color-adjust: exact;
                                }
                                img {
                                    max-width: 80%;
                                }
                            }
                        </style>
                    </head>
                    <body>
                        <img src="${imageSrc}" alt="Student ID Card">
                        <script>
                            window.onload = function() {
                                window.print();
                                window.onafterprint = function() {
                                    window.close();
                                };
                            };
                        </script>
                    </body>
                </html>
            `);
            printWindow.document.close();
            Swal.fire({
                icon: 'info',
                title: 'Print Dialog Opened!',
                text: 'Please select your printer and print settings.',
                timer: 2000,
                showConfirmButton: false
            });
        }