<?php
if($_SERVER['REQUEST_METHOD'] === 'POST'){
    require_once __DIR__ . '/../vendor/autoload.php';
    require_once __DIR__ . '/../admin/includes/db.php';

    $mpdf = new \Mpdf\Mpdf();
    header('Content-Type: application/pdf');


    $stmt = $pdo->prepare("SELECT * FROM users");
    $stmt->execute();
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $count = 1;

    $html ='
        <html>
            <head>
                <style>
                    body{
                        font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
                        font-size: 12px;
                        padding:20px;
                        color: #333;
                    } 
                    
                    h4{
                    text-align: center;
                    margin-bottom: 28px;
                    
                    }
                    table {
                        width: 100%;
                        border-collapse: collapse;
                        font-size: 11px;
                    }
                    th, td {
                        border: 1px solid black;
                        padding: 8px;
                        background-color: #E5EEE4;
                        text-align:left;
                    }

                    .signature-section{
                        marging-top: 28px;
                        display: flex;
                        justify-content: space-between;
                        font-size: 12px;
                    }

                    .signature{
                        width: 50%;
                        text-align: center;
                    }
                </style>
            </head>
            <body>
                <h4> Users</h4>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>First Name</th>
                            <th>Last Name</th>
                            <th>Username</th>
                            <th>Email</th>
                        </tr>
                    </thead>    
                
            <tbody>';

            foreach($users as $users){
                $html .='
                        <tr>
                            <td> ' . $count++ . '</td>
                            <td> ' . htmlspecialchars($users['firstname']) . '</td>
                            <td> ' . htmlspecialchars($users['lastname']) . '</td>
                            <td> ' . htmlspecialchars($users['username']) . '</td>
                            <td> ' . htmlspecialchars($users['email']) . '</td>
                        </tr>';
            }

            $html .= '
                </tbody>
                    </table>

                    <div class="signature-section">
                        <div class="signature">
                            <p>________________________________________________</p>
                            <p><strong> Admin </strong></p>
                        </div>
                    </div>
                </body>
            </html>';

        $mpdf->SetHTMLFooter('
        <div style="text-align: left;">
            Page {PAGENO}/{nbpg}
        </div>');


        $mpdf->WriteHTML($html);
        $mpdf->Output('', 'I');
        exit;

}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Print Users</title>
</head>
<body>

<form method="POST" action="">
    <button type="submit">Print Users</button>
</form>

</body>
</html>