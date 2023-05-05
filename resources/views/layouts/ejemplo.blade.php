<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
    <link href="https://cdn.jsdelivr.net/npm/simple-datatables@latest/dist/style.css" rel="stylesheet" type="text/css">
    <script src="https://cdn.jsdelivr.net/npm/simple-datatables@latest" type="text/javascript"></script>

<script>
        //import {DataTable} from "simple-datatables"

        const dataTable = new simpleDatatables.DataTable("table",{
        searchable: true,
        fixedHeight: true,
    });

        //let newRow = ["Cell 1", "Cell 2", "Cell 3"];

        //dataTable.rows.add(newRow);
    </script>
</head>
<body>
    <div class="mt-3 mx-5">
        <div class="table-responsive">
            <table class="table table-sm table-striped" id="table">
                <thead class="table-dark">
                    <tr>
                        <th>Name</th>
                        <th>Job Title</th>
                        <th>Company</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Raymond Coslett</td>
                        <td>Teacher</td>
                        <td>Heller, Wehner and Wisozk</td>
                    </tr>
                    <tr>
                        <td>Sol Trunby</td>
                        <td>Nuclear Power Engineer</td>
                        <td>Borer, Kris and Greenholt</td>
                    </tr>
                    <tr>
                        <td>Syd Hardiman</td>
                        <td>Engineer I</td>
                        <td>Ruecker-Okuneva</td>
                    </tr>
                    <tr>
                        <td>Melony Kewley</td>
                        <td>Clinical Specialist</td>
                        <td>Reichel LLC</td>
                    </tr>
                    <tr>
                        <td>Taryn Thomel</td>
                        <td>Social Worker</td>
                        <td>Baumbach-Hills</td>
                    </tr>
                    <tr>
                        <td>Bernardo Kener</td>
                        <td>Tax Accountant</td>
                        <td>Harber and Sons</td>
                    </tr>
                    <tr>
                        <td>Sibley Sesons</td>
                        <td>Occupational Therapist</td>
                        <td>Jakubowski-Yost</td>
                    </tr>
                    <tr>
                        <td>Marylou Rackam</td>
                        <td>Senior Developer</td>
                        <td>Wisoky-Hammes</td>
                    </tr>
                    <tr>
                        <td>Ingeberg Toolin</td>
                        <td>Clinical Specialist</td>
                        <td>Wisozk, Rau and Blick</td>
                    </tr>
                    <tr>
                        <td>Luella Chesterton</td>
                        <td>Geologist III</td>
                        <td>Pacocha, Glover and Howe</td>
                    </tr>
                    <tr>
                        <td>Jamaal Martineau</td>
                        <td>Nurse Practicioner</td>
                        <td>Johnston and Sons</td>
                    </tr>
                    <tr>
                        <td>Joey Rosel</td>
                        <td>Legal Assistant</td>
                        <td>Marvin, Schaden and Konopelski</td>
                    </tr>
                    <tr>
                        <td>Serge Guinn</td>
                        <td>Payment Adjustment Coordinator</td>
                        <td>Walker and Sons</td>
                    </tr>
                    <tr>
                        <td>Dino Pilipets</td>
                        <td>Physical Therapy Assistant</td>
                        <td>Cremin LLC</td>
                    </tr>
                    <tr>
                        <td>Brady Paolicchi</td>
                        <td>Human Resources Manager</td>
                        <td>Bosco Group</td>
                    </tr>
                    <tr>
                        <td>Ame Nevins</td>
                        <td>Programmer Analyst III</td>
                        <td>Spinka-Ledner</td>
                    </tr>
                    <tr>
                        <td>Huntington Wankling</td>
                        <td>Executive Secretary</td>
                        <td>Botsford and Sons</td>
                    </tr>
                    <tr>
                        <td>Ely Lapham</td>
                        <td>Programmer IV</td>
                        <td>Ullrich, Mohr and Schowalter</td>
                    </tr>
                    <tr>
                        <td>Davy Artingstall</td>
                        <td>Office Assistant III</td>
                        <td>Donnelly, DuBuque and Raynor</td>
                    </tr>
                    <tr>
                        <td>Chilton Arger</td>
                        <td>Physical Therapy Assistant</td>
                        <td>Carter-Schneider</td>
                    </tr>
                    <tr>
                        <td>Carmel Syred</td>
                        <td>Software Engineer II</td>
                        <td>Mertz Inc</td>
                    </tr>
                    <tr>
                        <td>Ardelia Rief</td>
                        <td>Sales Associate</td>
                        <td>Cummings LLC</td>
                    </tr>
                    <tr>
                        <td>Elke McConnachie</td>
                        <td>Legal Assistant</td>
                        <td>Corwin-O'Reilly</td>
                    </tr>
                    <tr>
                        <td>Tabbie Prendiville</td>
                        <td>Human Resources Manager</td>
                        <td>Kunze-Waelchi</td>
                    </tr>
                    <tr>
                        <td>Nico Domb</td>
                        <td>Physical Therapy Assistant</td>
                        <td>Hintz, Johns and Raynor</td>
                    </tr>
                    <tr>
                        <td>Terrill Jerzak</td>
                        <td>Accounting Assistant II</td>
                        <td>Gleichner-Stokes</td>
                    </tr>
                    <tr>
                        <td>Deb Chilley</td>
                        <td>Staff Scientist</td>
                        <td>Gusikowski-Rempel</td>
                    </tr>
                    <tr>
                        <td>Madge Roberti</td>
                        <td>Geological Engineer</td>
                        <td>Batz LLC</td>
                    </tr>
                    <tr>
                        <td>Vanna Beecheno</td>
                        <td>Dental Hygienist</td>
                        <td>Rippin, McGlynn and Hartmann</td>
                    </tr>
                    <tr>
                        <td>Bevvy Fetter</td>
                        <td>Tax Accountant</td>
                        <td>Ryan LLC</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>