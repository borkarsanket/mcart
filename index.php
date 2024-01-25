<?php

function connection()
{
    $servername = "13.127.134.233";
    $username = "root";
    $password = "pass@123";
    $dbname = "CovidDB";

    $conn = new mysqli($servername, $username, $password, $dbname);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}
function excexuteQuery($type)
{

    $result  = array('head' => array(), 'body' => array());
    $conn = connection();
    if ($type == "tot_continent") {
        $result['head'] = array('Sl No', 'Total Cases', 'Continent');
        $query = "SELECT SUM(total_cases) AS continent_sum,continent FROM ProcessedData GROUP BY continent ";
    } else if ($type == "tot_numb_death") {
        $result['head'] = array('Sl No', 'Total Death', 'Location');
        $query = "SELECT SUM(total_deaths) AS continent_sum,continent FROM ProcessedData GROUP BY continent";
    } else if ($type == "tot_num_vacc_loc") {
        $result['head'] = array('Sl No', 'Vaccinated People', 'Location');
        $query = "SELECT SUM(people_vaccinated),location FROM ProcessedData GROUP BY location ORDER BY location ASC";
    } else if ($type == "max_death_eur_asia") {
        $result['head'] = array('Sl No', 'Maximum Death', 'Continent');
        $query = "SELECT SUM(total_deaths),continent FROM ProcessedData WHERE continent IN('Europe','Asia') GROUP BY continent ORDER BY continent ASC";
    } else if ($type == "country_vacc_jan_2021") {
        $result['head'] = array('Sl No', 'Vaccination', 'Location');
        $query = "SELECT total_vaccinations,location FROM ProcessedData where DATE_FORMAT(date_current,'%Y %M') = '2021 January' ";
    } elseif ($type == 'extract_data') {
        $sqldata = $conn->query('TRUNCATE TABLE ProcessedData');
        $sqldata = $conn->query("INSERT INTO ProcessedData (continent, location, date_current,total_cases,total_deaths,total_vaccinations,people_vaccinated)
		SELECT continent, location, DATE_FORMAT(STR_TO_DATE(date_current, '%m/%d/%Y'), '%Y-%m-%d'),total_cases,total_deaths,total_vaccinations,people_vaccinated
		FROM CovidData");
        echo "<p style='color:green;text-align:center'>Data ProcessedData Successfully</p>";
        return true;
    }
    $sqldata = $conn->query($query);
    if ($sqldata->num_rows > 0) {
        while ($row = $sqldata->fetch_assoc()) {
            $result['body'][] = $row;
        }
    }

    $conn->close();
    return $result;
}

if (!empty($_POST)) {
    $resultData = excexuteQuery($_POST['sumbit']);
}
?>
<html>

<head>
    <title>Covid DB</title>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/css/bootstrap.min.css">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.4.1/js/bootstrap.min.js"></script>
</head>

<body>
    <div class="container">
        <h3>Covid DB</h3>
        <form method="POST" action="<?php echo $PHP_SELF; ?>">
            <table class="table table-hover">
                <thead>
                    <tr>
                        <th>Details</th>
                        <th class="text-center">Action</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Extract data ‘ProcessedData’ table</td>
                        <td class="text-center"><button type="submit" value="extract_data" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'extract_data') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                    <tr>
                        <td>Total number of cases in each Continent</td>
                        <td class="text-center"><button type="submit" value="tot_continent" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'tot_continent') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                    <tr>
                        <td>Find the total number of deaths in each location</td>
                        <td class="text-center"><button type="submit" value="tot_numb_death" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'tot_numb_death') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                    <tr>
                        <td>Maximum deaths at specific locations like Europe and Asia</td>
                        <td class="text-center"><button type="submit" value="max_death_eur_asia" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'max_death_eur_asia') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                    <tr>
                        <td>Total number of people vaccinated at each Location</td>
                        <td class="text-center"><button type="submit" value="tot_num_vacc_loc" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'tot_num_vacc_loc') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                    <tr>
                        <td>Country wise vaccination for the month January 2021</td>
                        <td class="text-center"><button type="submit" value="country_vacc_jan_2021" name="sumbit" class="<?php echo ($_POST['sumbit'] == 'country_vacc_jan_2021') ? "btn btn-primary" : "btn btn-success"; ?>">Click</button></td>
                    </tr>
                </tbody>
            </table>
        </form>
        <?php if (is_array($resultData)) : ?>
            <div class="panel panel-default">
                <div class="panel-body">
                    <table class="table table-hover">
                        <thead>
                            <?php if (!empty($resultData['head'])) : ?>
                                <tr>
                                    <?php foreach ($resultData['head'] as $eachData) {
                                        echo '<th>';
                                        echo $eachData;
                                        echo '</th>';
                                    }
                                    ?>
                                </tr>
                            <?php endif; ?>
                            <?php if (!empty($resultData['body'])) : ?>
                                <tr>
                                    <?php $k = 1;
                                    foreach ($resultData['body'] as $eachBodyData) {
                                        echo '<tr><td>' . $k . '</td>';
                                        foreach ($eachBodyData as $eachItem) {
                                            echo '<td>';
                                            echo $eachItem;
                                            echo '</td>';
                                        }
                                        ++$k;
                                        echo '<tr>';
                                    }


                                    ?>
                                </tr>
                            <?php else : ?>
                                <tr>
                                    <td colspan="3">No results found..</td>
                                </tr>
                            <?php endif; ?>
                        </thead>
                    </table>
                </div>
            </div>
        <?php endif; ?>
    </div>
</body>

</html>
