<?php

error_reporting(E_ALL);
ini_set("display_errors", E_ALL);

include "globalvars.php";

// ==================================================================
// =                       INITIALIZATION                           =
// ==================================================================

/* Get database connection, or initialise it */
function  db_connection() {

    static $conn;

    if ($conn === NULL){

        // Use the global variables as defined in globalvars.php
        global $server, $user, $password, $database;

        $conn = mysqli_connect($server, $user, $password);

        if ($conn->select_db($database) == 0) {

            $conn = mysqli_connect($server, $user, $password);
            $conn->query("CREATE DATABASE " . $database);
            $conn->select_db($database);

            create_tables($conn);
            populate_sectors($conn);
            populate_stocks($conn);
        }

    }

    return $conn;
}

function create_tables($conn) {

    if (create_sectors($conn) && create_stocks($conn) && create_queries($conn) &&
        create_history($conn) && create_fav_stocks($conn) && create_fav_sectors($conn) &&
        create_last_pinged_stocks($conn) && create_last_pinged_sectors($conn)) {
        echo "Database created successfully!<br>";
        return 1;
    } else {
        echo $conn->error . "<br>";
        return 0;
    }

}

function create_sectors($conn) {

    // Create sectors
    $sql = "CREATE TABLE IF NOT EXISTS sectors (
        sector_id       integer NOT NULL AUTO_INCREMENT,
        sector_name     varchar(50),
        scrape_url      varchar(512),
        PRIMARY KEY (sector_id)
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table sectors: " . $conn->error . "<br>";
        return 0;
    }

}

function create_stocks($conn) {

    // Create stocks
    $sql = "CREATE TABLE IF NOT EXISTS stocks (
        stock_id        integer NOT NULL AUTO_INCREMENT,
        stock_name      varchar(50),
        ticker_symbol   varchar(4),
        sector_id       integer not null,
        scrape_url      varchar(512),
        PRIMARY KEY (stock_id),
        FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table stocks: " . $conn->error . "<br>";
        return 0;
    }

}

function create_queries($conn) {

    // Create queries
    $sql = "CREATE TABLE IF NOT EXISTS queries (
        query_id    integer NOT NULL AUTO_INCREMENT,
        query_str   varchar(128),
        intent      varchar(64),
        entity      varchar(32),
        PRIMARY KEY (query_id)
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table queries: " . $conn->error . "<br>";
        return 0;
    }

}

function create_history($conn) {

    // Create history
    $sql = "CREATE TABLE IF NOT EXISTS history (
        query_id    integer not null,
        frequency   integer,
        last_asked  Date,
        FOREIGN KEY (query_id) REFERENCES queries(query_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table history: " . $conn->error . "<br>";
        return 0;
    }

}

function create_fav_stocks($conn) {

    // Create fav_stocks
    $sql = "CREATE TABLE IF NOT EXISTS fav_stocks (
        stock_id    integer,
        date_added  Date,
        notif_freq  varchar(25),
        FOREIGN KEY (stock_id) REFERENCES stocks(stock_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table fav_stocks: " . $conn->error . "<br>";
        return 0;
    }

}

function create_fav_sectors($conn) {

    // Create fav_sectors
    $sql = "CREATE TABLE IF NOT EXISTS fav_sectors (
        sector_id   integer,
        date_added  Date,
        FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table fav_sectors: " . $conn->error . "<br>";
        return 0;
    }

}

function create_last_pinged_stocks($conn) {

    $sql = "CREATE TABLE IF NOT EXISTS last_pinged_stocks (
        stock_id        integer,
        last_ping       DateTime,
        recommendation  varchar(4),
        FOREIGN KEY (stock_id) REFERENCES stocks(stock_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table last_pinged_stocks: " . $conn->error . "<br>";
        return 0;
    }

}

function create_last_pinged_sectors($conn) {

    $sql = "CREATE TABLE IF NOT EXISTS last_pinged_sectors (
        sector_id       integer,
        last_ping       DateTime,
        recommendation  varchar(4),
        FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table last_pinged_sectors: " . $conn->error . "<br>";
        return 0;
    }

}

/* ==================================================================
 * =                           POPULATION                           =
 * ==================================================================
 */
function populate_sectors($conn) {
    $sql = "INSERT INTO sectors (sector_name, scrape_url) VALUES
        ('Aerospace & Defence',                 '/indices/aerospace---defense'),
        ('Automobile & Parts',                  '/indices/ftse-350-automobiles---parts'),
        ('Banks',                               '/indices/banks'),
        ('Beverages',                           '/indices/beverages'),
        ('Chemicals',                           '/indices/chemicals'),
        ('Construction & Materials',            '/indices/construction---mats'),
        ('Electricity',                         '/indices/electricity'),
        ('Electronic & Electrical Equipment',   '/indices/electronic-equipment'),
        ('Equity Investment Instruments',       '/indices/equity-investment-instruments'),
        ('Financial Services',                  '/indices/ftse-350-financial-services'),
        ('Fixed Line Telecommunications',       '/indices/fixed-line-telecomms'),
        ('Food & Drug Retailers',               '/indices/food---drug-retailers'),
        ('Food Producers',                      '/indices/food-producers'),
        ('Forestry & Paper',                    '/indices/ftse-350-forestry---paper'),
        ('Gas, Water & Multiutilities',         '/indices/gas,-water---multiutilities'),
        ('General Industrials',                 '/indices/ftse-350-general-industrials'),
        ('General Retailers',                   '/indices/general-retailers'),
        ('Health Care Equipment & Services',    '/indices/ftse-350-health-care-eq.---serv.'),
        ('Household goods',                     '/indices/household-goods'),
        ('Industrial Engineering',              '/indices/industrial-engineering'),
        ('Industrial Metals & Mining',          '/indices/ftse-350-ind.-metals---mining'),
        ('Industrial Transportation',           '/indices/industrial-transportation'),
        ('Life assurance',                      '/indices/life-assurance'),
        ('Media',                               '/indices/media---photo'),
        ('Mining',                              '/indices/mining'),
        ('Mobile Telecommunications',           '/indices/ftse-350-mobile-telecommunications'),
        ('Non-life insurance',                  '/indices/nonlife-insurance'),
        ('Oil & Gas Producers',                 '/indices/oil---gas'),
        ('Oil Equipment & Services',            '/indices/oil-equipment'),
        ('Personal Goods',                      '/indices/ftse-350-personal-goods'),
        ('Pharmaceuticals & Biotechnology',     '/indices/pharmaceuticals---biotech'),
        ('Real Estate Investment & Services',   '/indices/ftse-supersector-real-estat'),
        ('Real Estate Investment Trusts',       '/indices/ftse-350-reits'),
        ('Retail hospitality',                  '/indices/'),
        ('Software & Computer Services',        '/indices/software---comp-services'),
        ('Support services',                    '/indices/support-services'),
        ('Tobacco',                             '/indices/tobacco'),
        ('Travel & Leisure',                    '/indices/travel---leisure'),
        ('FTSE100',                             '/indices/uk-100')
";

    if ($conn->query($sql) === TRUE) {
        echo "Sectors added successfully <br>";
    } else {
        echo "Error populating sectors: " . $conn->error . "<br>";
    }

}

function populate_stocks($conn) {

    $sql = "INSERT INTO stocks (stock_name, ticker_symbol, sector_id, scrape_url) VALUES
        ('3i',                                  'III',	10,	'/equities/3i'),
        ('Admiral Group',                       'ADM',	27,	'/equities/admiral-group'),
        ('Anglo American plc',                  'AAL',	25,	'/equities/anglo-american'),
        ('Antofagasta',                         'ANTO',	25,	'/equities/antofagasta'),
        ('Ashtead Group',                       'AHT',	36,	'/equities/ashtead-group'),
        ('Associated British Foods',            'ABF',	13,	'/equities/assoc.br.foods'),
        ('AstraZeneca',                         'AZN',	31,	'/equities/astrazeneca'),
        ('Aviva',                               'AV.',	21,	'/equities/aviva'),
        ('BAE Systems',                         'BA.',	1,	'/equities/bae-systems'),
        ('Barclays',                            'BARC',	3,	'/equities/barclays'),
        ('Barratt Developments',                'BDEV',	19,	'/equities/barratt-developments'),
        ('Berkeley Group Holdings',             'BKG',	19,	'/equities/berkeley-group-holdings-plc'),
        ('BHP',	                                'BLT',	25,	'/equities/bhp-billiton'),
        ('BP',	                                'BP.',	28,	'/equities/bp'),
        ('British American Tobacco',            'BATS',	37,	'/equities/british-american-tobacco'),
        ('British Land',                        'BLND',	33,	'/equities/british-land'),
        ('BT Group',                            'BT.A',	11,	'/equities/bt-group'),
        ('Bunzl',                               'BNZL',	36,	'/equities/bunzl'),
        ('Burberry',                            'BRBY',	30,	'/equities/burberry'),
        ('Carnival Corporation & plc',          'CCL',	38,	'/equities/carnival-corporation'),
        ('Centrica',                            'CNA',	15,	'/equities/centrica'),
        ('Coca-Cola',                           'CCH',	4,	'/equities/cocacola-hb'),
        ('Compass Group',                       'CPG',	38,	'/equities/compass-group'),
        ('CRH plc',                             'CRH',	6,	'/equities/crh'),
        ('Croda International',                 'CRDA',	5,	'/equites/croda'),
        ('DCC plc',                             'DCC',	36,	'/equities/dcc-plc-exch'),
        ('Diageo',                              'DGE',	4,	'/equities/diageo'),
        ('Direct Line Group',                   'DLG',	27,	'/equities/direct-line'),
        ('easyJet',                             'EZJ',	38,	'/equities/easyjet'),
        ('Evraz',                               'EVR',	21,	'/equities/evraz'),
        ('Experian',                            'EXPN',	36,	'/equities/experian-ord-usd0'),
        ('Ferguson plc',                        'FERG',	36,	'/equities/wolseley'),
        ('Fresnillo plc',                       'FRES',	25,	'/equities/fresnillo'),
        ('G4S',                                 'GFS',	36,	'/equities/group-4-securicor'),
        ('GKN',	                                'GKN',	2,	'/equities/gkn'),
        ('GlaxoSmithKline',                     'GSK',	31,	'/equities/glaxosmithkline'),
        ('Glencore',                            'GLEN',	25,	'/equities/glencore'),
        ('Halma',                               'HLMA',	8,	'/equities/halma'),
        ('Hammerson',                           'HMSO',	33,	'/equities/hammerson'),
        ('Hargreaves Lansdown',                 'HL.',	10,	'/equities/hargreaves'),
        ('HSBC',	                            'HSBA',	3,	'/equities/hsbc-holdings'),
        ('Imperial Brands',                     'IMB',	37,	'/equities/imperial-tobacco'),
        ('Informa',                             'INF',	24,	'/equities/informa'),
        ('InterContinental Hotels Group',       'IHG',	38,	'/equities/intercontinental-hotels-group'),
        ('International Airlines Group',        'IAG',	38,	'/equities/intl.-cons.-air-grp'),
        ('Intertek',                            'ITRK',	36,	'/equities/intertek-testing-services'),
        ('ITV plc',                             'ITV',	24,	'/equities/itv'),
        ('Johnson Matthey',                     'JMAT',	5,	'/equities/johnson-matthey'),
        ('Just Eat',                            'JE.',	17,	'/equities/just-eat'),
        ('Kingfisher plc',                      'KGF',	17,	'/equities/kingfisher'),
        ('Land Securities',                     'LAND',	33,	'/equities/land-securities'),
        ('Legal & General',                     'LGEN',	21,	'/equities/legal---general'),
        ('Lloyds Banking Group',                'LLOY',	3,	'/equities/lloyds-banking-grp'),
        ('London Stock Exchange Group',         'LSE',	10,	'/equities/london-stock-exchange'),
        ('Marks & Spencer',                     'MKS',	17,	'/equities/marks---spencer-group'),
        ('Mediclinic International',            'MDC',	18,	'/equities/al-noor-hosp'),
        ('Micro Focus',                         'MCRO',	35,	'/equities/micro-focus'),
        ('Mondi',                               'MNDI',	14,	'/equities/mond'),
        ('Morrisons',                           'MRW',	12,	'/equities/william-morrison'),
        ('National Grid plc',                   'NG.',	15,	'/equities/national-grid'),
        ('Next plc',                            'NXT',	17,	'/equities/next'),
        ('NMC Health',                          'NMC',	18,	'/equities/nmc-health'),
        ('Old Mutual',                          'OML',	21,	'/equities/old-mutual'),
        ('Paddy Power Betfair',                 'PPB',	38,	'/equities/paddy-power'),
        ('Pearson',                             'PSON',	24,	'/equities/pearson'),
        ('Persimmon plc',                       'PSN',	19,	'/equities/persimmon'),
        ('Prudential plc',                      'PRU',	21,	'/equities/prudential'),
        ('Randgold Resources',                  'RRS',	25,	'/equities/randgold-resources'),
        ('Reckitt Benckiser',                   'RB.',	19,	'/equities/reckitt-benckiser'),
        ('RELX Group',                          'REL',	24,	'/equities/reed-elsevier'),
        ('Rentokil Initial',                    'RTO',	36,	'/equities/rentokil-initial'),
        ('Rio Tinto Group',                     'RIO',	25,	'/equities/rio-tinto'),
        ('Rolls-Royce Holdings',                'RR.',	1,	'/equities/rolls-royce'),
        ('The Royal Bank of Scotland Group',    'RBS',	3,	'/equities/royal-bank-of-scotland'),
        ('Royal Dutch Shell A',                 'RDSA',	28,	'/equities/royal-dutch-shell-a-shr?cid=6593'),
        ('Royal Dutch Shell B',                 'RDSB',	28,	'/equities/royal-dutch-shell-b-shr?cid=8751'),
        ('RSA Insurance Group',                 'RSA',	27,	'/equities/royal---sun-alliance'),
        ('Sage Group',                          'SGE',	35,	'/equities/sage-group'),
        ('Sainsbury''s',                        'SBRY',	12,	'/equities/sainsbury'),
        ('Schroders',                           'SDR',	10,	'/equities/schroders'),
        ('Scottish Mortgage Investment Trust',  'SMT',	9,	'/equities/scottish-mortgage-inv-trust'),
        ('Segro',                               'SGRO',	33,	'/equities/scottish---southern-energy'),
        ('Severn Trent',                        'SVT',	15,	'/equities/severn-trent'),
        ('Shire plc',                           'SHP',	31,	'/equities/shire'),
        ('Sky plc',                             'SKY',	24,	'/equities/bskyb'),
        ('Smith & Nephew',                      'SN.',	18,	'/equities/smith-and-nephew'),
        ('Smith',                               'SMDS',	16,	'/equities/ds-smith'),
        ('Smiths Group',                        'SMIN',	16,	'/equities/smiths-group'),
        ('Smurfit Kappa',                       'SKG',	16,	'/equities/smurfit-kappa-group'),
        ('SSE plc',                             'SSE',	7,	'/equities/scottish---southern-energy'),
        ('Standard Chartered',                  'STAN',	3,	'/equities/standard-chartered'),
        ('Standard Life Aberdeen',              'SLA',	10,	'/equities/standard-life'),
        ('St. James''s Place plc',              'STJ',	21,	'/equities/st-james'),
        ('Taylor Wimpey',                       'TW.',	19,	'/equities/taylor-wimpey'),
        ('Tesco',                               'TSCO',	12,	'/equities/tesco'),
        ('TUI Group',                           'TUI',	38,	'/equities/tui-n?cid=23214'),
        ('Unilever',                            'ULVR',	30,	'/equities/unilever'),
        ('United Utilities',                    'UU.',	15,	'/equities/united-utilities'),
        ('Vodafone Group',                      'VOD',	26,	'/equities/vodafone'),
        ('Whitbread',                           'WTB',	34,	'/equities/whitbread'),
        ('WPP plc',                             'WPP',	24,	'/equities/wpp')";

    if ($conn->query($sql) === TRUE) {
        echo "Stocks inserted successfully <br>";
    } else {
        echo "Error inserting stocks: " . $conn->error . "<br>";
    }

}

/* ==================================================================
 * =                             RESET                              =
 * ==================================================================
 */
function reset_db($conn) {

    if (drop_tables($conn) && drop_database($conn)) {
        echo "Database reset<br>";
        return 1;
    } else {
        echo "Error reseting database: " . $conn->error . "<br>";
        return 0;
    }

}

function drop_tables($conn) {

    $sql = "DROP TABLE last_pinged_stocks, last_pinged_sectors, fav_sectors, fav_stocks, history, queries, stocks, sectors";

    if ($conn->multi_query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

}

function drop_database($conn) {

    $sql = "DROP DATABASE traderbot_db";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error:" . $conn->error . "<br>";
        return 0;
    }

}

// ==================================================================
// *                           INSERTION                            =
// ==================================================================

/* Insert data into QUERIES table */
function insert_query($conn, $query_str, $intent, $entity) {

    $exists = "SELECT * FROM queries WHERE intent = '" . $intent . "' AND entity = '" . $entity . "'";
    $res = $conn->query($exists);

    if ($res->num_rows <= 0) {
        $sql = "INSERT INTO queries (query_str, intent, entity) VALUES ('" . $query_str . "','" . $intent . "','" . $entity . "')";

        if ($conn->query($sql) !== TRUE) {
            echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
            return 0;
        }

        $inserted_id = $conn->insert_id;
        insert_history($conn, $inserted_id);

    } else {
        // Query is already in table, so add new query in HISTORY
        $get_query_id = "SELECT query_id FROM queries WHERE intent = '" . $intent . "' AND entity = '" . $entity . "'";
        $res = $conn->query($get_query_id);
        $row = $res->fetch_assoc();

        insert_history($conn, $row["query_id"]);
    }


}

/* Insert data into HISTORY table */
function insert_history($conn, $inserted_id) {

    // Test if query is in history already
    $hist_exist = "SELECT * FROM history WHERE query_id = " . $inserted_id;
    $res = $conn->query($hist_exist);

    if ($res->num_rows > 0) {
        $sql = "UPDATE history SET frequency = frequency+1, last_asked = '" . date("Y-m-d") . "' WHERE query_id = '" . $inserted_id . "'";
    } else {
        $sql = "INSERT INTO history (query_id,frequency,last_asked) VALUES (" . $inserted_id . ",1,'" . date("Y-m-d") . "')";
    }

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql. "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}/* else {
echo $existence. "<br>Error executing query: " . $conn->error . "<br>";
return 0;
    }*/



/* Insert into fav_stocks using stock_name */
function insert_fav_stock($conn, $stock_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT stock_id,'" . $date . "','" . $freq . "' FROM stocks WHERE stock_name = '" . $stock_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }
}

/* Insert into fav_stocks using the stock_id */
function insert_fav_stock_id($conn, $stock_id, $freq) {

    $date = date("Y-m-d");

    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) VALUES (" . $stock_id . ",'" . $date . "','". $freq . "')";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}

/* Insert into fav_sectors using sector_name */
function insert_fav_sector($conn, $sector_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT sector_id,'" . $date . "','" . $freq . "' FROM sectors WHERE sector_name = '" . $sector_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}

/* Insert into fav_sectors using the sector_id */
function insert_fav_sector_id($conn, $sector_id) {

    $date = date("Y-m-d");

    $sql = "INSERT INTO fav_sectors (sector_id, date_added) VALUES (" . $sector_id . ",'" . $date . "')";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}

function insert_last_ping_stock($conn, $stock_id, $recommendation) {

    $datetime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO last_pinged_stocks (stock_id, last_ping, recommendation) VALUES (" . $stock_id . ",'" . $datetime . "','Not Selected')";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error inserting to last_pinged_stocks: " . $conn->error . "<br>";
        return 0;
    }

}

function insert_last_ping_sector($conn, $sector_id, $recommendation) {

    $datetime = date("Y-m-d H:i:s");

    $sql = "INSERT INTO last_pinged_sectors (sector_id, last_ping, recommendation) VALUES (" . $sector_id . ",'" . $datetime . "','Not Selected')";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error inserting to last_pinged_sectors: " . $conn->error . "<br>";
        return 0;
    }

}

function update_last_ping_stock($conn, $stock_id) {

    $datetime = date("Y-m-s H:i:s");

    $sql = "UPDATE last_pinged_stocks SET last_ping = '" . $datetime . "' WHERE stock_id = " . $stock_id;

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}

function update_last_ping_sector($conn, $sector_id) {

    $datetime = date("Y-m-s H:i:s");

    $sql = "UPDATE last_pinged_sectors SET last_ping = '" . $datetime . "' WHERE sector_id = " . $sector_id;

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error executing query: " . $conn->error . "<br>";
        return 0;
    }

}

function get_recommendations($conn, $json) {

    // TODO
    include_once("../ParsingAndProcessing/getBuyOrSell.php");

    $new_recommendations = array();

    $data = json_decode($json, TRUE);
    $companies = $data["companyList"];

    foreach ($companies as $c) {

        $sql = "SELECT ticker_symbol FROM stocks WHERE stock_id = " . $c["id"];
        $res = $conn->query($sql);

        $row = $res->fetch_assoc();
        $ticker = $row["ticker_symbol"];

        if (strcmp($c["poll_rate"], "15 Minutes") == 0)
            $time = "15m";
        else if (strcmp($c["poll_rate"], "1 Hour") == 0)
            $time = "1h";
        else if (strcmp($c["poll_rate"], "1 Day") == 0)
            $time = "1D";
        else
            $time = "5m";

        $recommendations = getBuyOrSell($ticker, $time);
        $buysell = strtolower($recommendations["Summary"]);

        $sql = "SELECT recommendation FROM last_pinged_stocks WHERE stock_id = " . $c["id"];
        $res = $conn->query($sql);

        $row = $res->fetch_assoc();
        if (strcmp(strtolower($row["recommendation"]), $buysell) != 0) {
            update_last_ping_stock($conn, $c["id"]);
            $companies[] = $c["id"];
        }

    }

    return json_encode($new_recommendations);

}

// ==================================================================
// =                           PROCESSING                           =
// ==================================================================

/* Return JSON object of all favourite stocks and sectors */
function get_faves($conn) {

    // Array to hold all stocks and sectors
    $fav_list = array();
    $company_list = array();
    $sector_list = array();

    // Returns all stocks, with a 1 in column 'fav' if stock is in fav_stocks, 0 otherwise
    $sql = "SELECT stock_id AS sid, ticker_symbol, stock_name, IF (stock_id IN (SELECT stock_id FROM fav_stocks), 1, 0) AS fav, IF (stock_id IN (SELECT stock_id FROM fav_stocks),(SELECT notif_freq FROM fav_stocks WHERE stock_id = sid), 'Not Selected') AS poll_rate FROM stocks";
    // $sql = "SELECT stock_id, ticker_symbol, stock_name, IF (stock_id IN (SELECT stock_id FROM fav_stocks), 1, 0) AS fav FROM stocks";

    $res = $conn->query($sql);
    while ($row = $res->fetch_assoc()) {
        $company_list[] = array(
            "id" => $row["sid"],
            "ticker" => $row["ticker_symbol"],
            "name" => $row["stock_name"],
            "fav" => $row["fav"],
            "poll_rate" => $row["poll_rate"]
        );

    }

    // Returns all sectors, with a 1 in column 'fav' if sector is in fav_sectors, 0 otherwise
    $sql = "SELECT sector_id as secid, sector_name, IF (sector_id IN (SELECT sector_id FROM fav_sectors), 1, 0) AS fav FROM sectors";
    // $sql = "SELECT sector_id, sector_name, IF (sector_id IN (SELECT sector_id FROM fav_sectors), 1, 0) AS fav FROM sectors";

    $res = $conn->query($sql);

    while ($row = $res->fetch_assoc()) {
        $sector_list[] = array(
            "id" => $row["secid"],
            "name" => $row["sector_name"],
            "fav" => $row["fav"]
        );
    }

    $fav_list["companyList"] = $company_list;
    $fav_list["sectorList"] = $sector_list;
    $faves = json_encode($fav_list);

    return $faves;

}

/* Return scrape_url of given entity */
function get_scrape_url($conn, $entity) {

    $find_table = "SELECT scrape_url FROM stocks WHERE ticker_symbol = '" . $entity . "'";
    $res = $conn->query($find_table);

    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
    } else {
        $the_other_table = "SELECT scrape_url FROM sectors WHERE sector_name = '" . $entity . "'";
        $res = $conn->query($the_other_table);
        $row = $res->fetch_assoc();
    }

    return $row["scrape_url"];

}

/* Update entries in fav_stocks and fav_sectors */
function update_fav_tables($conn, $json_obj) {

    if (array_key_exists("companyList", $json_obj)) {

        echo "COMPANY LIST EXISTS\n";
        $stock_list = $json_obj["companyList"];

        // ID, FAV, POLLRATE
        foreach ($stock_list as $row) {

            if (is_numeric($row["id"])) {
                echo "ROW ID IS NUMERIC<BR>";
            }

            if (intval($row["fav"]) == 0) {

                $sql = "DELETE FROM fav_stocks WHERE stock_id = " . intval($row["id"]);

                if ($conn->query($sql) === TRUE) {

                } else {
                    echo "DISASTER: " . $conn->error . "<BR>";
                }

            } else {

                // First test existence
                $exists = "SELECT stock_id FROM fav_stocks WHERE stock_id = " . intval($row["id"]);
                $res = $conn->query($exists);

                if ($res->num_rows > 0) {

                    // stock is in fav_stocks
                    $update_poll = "UPDATE fav_stocks SET notif_freq = '" .
                        $row["poll_rate"] . "' WHERE stock_id = " .
                        intval($row["id"]);

                    if ($conn->query($update_poll) !== TRUE) {
                        echo "DISASTER: " . $conn->error . "<BR>";
                    }

                } else {

                    // stock not yet in fav_stocks
                    insert_fav_stock_id($conn, intval($row["id"]), $row["poll_rate"]);

                }
            }
        }
    }

    if (array_key_exists("sectorList", $json_obj)) {

        $sector_list = $json_obj["sectorList"];

        foreach ($sector_list as $row) {

            if ($row["fav"] == 0) {

                $sql = "DELETE FROM fav_sectors WHERE sector_id = " . $row["id"];
                $conn->query($sql);

            } else {

                // Test existence
                $exists = "SELECT sector_id FROM fav_sectors WHERE sector_id = " . $row["id"];
                $res = $conn->query($exists);

                if (!$res)
                    trigger_error('Invalid query: ' . $conn->error);

                if ($res->num_rows <= 0) {

                    // sector not yet in fav_stock
                    insert_fav_sector_id($conn, $row["id"]);

                }
            }
        }
    }

}

/* Suggest queries for the top five entities in the database */
/*  Returns array of suggestions: intent, entity, and tracked status */
function suggest_query($conn) {

    // Weight function w: F x D -> N ; w(f,d) = f / (1 + CURDATE() - d)
    // Higher weight is better

    $sql = "SELECT query_id, query_str, intent, entity FROM queries AS t1 NATURAL JOIN (SELECT query_id, (frequency / (1 + CURDATE() - last_asked)) AS weight FROM history) as t2 ORDER BY t2.weight DESC LIMIT 5";

    $suggested = array();

    $res = $conn->query($sql);

    if ($res->num_rows > 0) {

        while ($row = $res->fetch_assoc()) {

            // Test for favouriteness
            $test_fav = "SELECT * FROM fav_stocks NATURAL JOIN (SELECT * FROM stocks INNER JOIN (SELECT * FROM queries) AS t0 ON ticker_symbol = entity) AS t1";
            $fav_res = $conn->query($test_fav);

            $suggestion = array(
                "intent" => $row["intent"],
                "entity" => $row["entity"],
                "tracked" => ($fav_res->num_rows > 0) ? "tracked" : "untracked"
            );

            array_push($suggested, $suggestion);

        }

    }

    return $suggested;

}


/* Return top 3 sectors that the user is interested in, based on what they track */
function learn_sectors($conn) {

    // Looks at all stocks tracked by user and learns which sectors they are focusing on
    $sql = "SELECT sector_id, COUNT(sector_id) FROM stocks WHERE sector_id IN (SELECT sector_id FROM fav_sectors) GROUP BY sector_id ORDER BY COUNT(sector_id) DESC LIMIT 3";

    $res = $conn->query($sql);      // Returns top three sectors whose stocks are tracked the most

    $top_sectors = array();

    /*
     * Create array of top three sectors e.g.
     *      $top_sectors[0] = "Banks"
     *      $top_sectors[1] = "Tobacco"
     *      $top_sectors[2] = "Media"
     */

    while ($row = $res->fetch_assoc()) {
        $top_sectors[] = $row["sector_id"];
    }

    return $top_sectors;

}

/* Suggest stocks to track based on the sectors learned */
function suggest_stock_track($conn, $sectors) {

    foreach ($sectors as $s) {

    }

}

// TODO: this
/* Return 3 stocks they do not currently track based on which sectors they track */
function learn_stocks($conn) {

    $sql = "";

}

// ==================================================================
// =                     ERROR CORRECTION                           =
// ==================================================================

function resolve_invalid_entity($conn, $entity) {

    return get_corrections($conn, $entity);

}

// TODO
//      write up notes of final report for database
//      finish learning
//
//      How are faves doing
//      from faves in persons table, get fave and call get buy or sell, return data set
//          BARC        BUY
//          MRW         SELL
//          etc...

/**
 * @param: string $word - intent/entity to correct
 * @param: array $dict - dictionary of legal words
 * @param: string $type - either "entity" or "intent"
 */
function edit_1($word, $dict, $type) {

    $word = strtolower($word);

    if (strcmp(strtolower($type), "entity") == 0) {
        $alpha = "abcdefghijklmnopqrstuvwxyz.& ";
    } else {
        $alpha = "abcdefghijklmnopqrstuvwxyz_";
    }

    $alpha = str_split($alpha);
    $n = strlen($word);

    $edits = array();

    for ($i = 0; $i < $n; $i++) {

        // Deleting one char
        $edits[] = substr($word, 0, $i).substr($word, $i+1);

        // Substituting one char
        foreach ($alpha as $c) {
            $edits[] = substr($word, 0, $i) . $c . substr($word, $i+1);
        }

    }

    for ($i = 0; $i < $n - 1; $i++) {

        // Swapping order of two chars
        $edits[] = substr($word, 0, $i).$word[$i+1].$word[$i].substr($word, $i+2);

    }

    for ($i = 0; $i < $n + 1; $i++) {

        // Inserting one char
        foreach ($alpha as $c) {
            $edits[] = substr($word, 0, $i) . $c . substr($word, $i);
        }

    }

    return $edits;
}

function edit_2($word, $dict, $type) {
    $known = array();

    foreach (edit_1($word, $dict, $type) as $e1) {
        foreach (edit_1($e1, $dict, $type) as $e2) {
            if (in_array($e2, $dict))
                $known[] = $e2;
        }
    }
    return $known;
}

function get_corrections($conn, $word, $type) {

    if (strcmp(strtolower($type), "entity") == 0) {

        $sql = "SELECT stock_name, ticker_symbol FROM stocks";
        $res = $conn->query($sql);

        $dict = array();
        $suggested = array();

        while ($row = $res->fetch_assoc()) {
            $key1 = strtolower($row["stock_name"]);
            $key2 = strtolower($row["ticker_symbol"]);
            $dict[] = $key1;
            $suggested[$key1] = 0;
            $dict[] = $key2;
            $suggested[$key2] = 0;
        }

        $sql = "SELECT sector_name FROM sectors";
        $res = $conn->query($sql);

        while ($row = $res->fetch_assoc()) {
            $key = strtolower($row["sector_name"]);
            $dict[] = $key;
            $suggested[$key] = 0;
        }

    } else {

        // TODO: find a way for invalid intents
        $sql = "SELECT intent FROM queries";
        $res = $conn->query($sql);

        $dict = array();
        $suggested = array();

        // Fill dict with all prev intents
        while ($row = $res->fetch_assoc()) {
            $key1 = strtolower($row["intent"]);
            $dict[] = $key1;
            $suggested[$key1] = 0;
        }


    }

    // Work out all words that are legal
    $known = edit_2($word, $dict, $type);
    foreach ($known as $k) {
        $suggested[$k] = 1;
    }

    $dinstinct = array();
    foreach ($suggested as $key => $in_dict) {
        if ($in_dict === 1) {
            if (strlen($key) < 5) {
                $distinct[] = strtoupper($key);
            } else {
                $distinct[] = $key;
            }
        }
    }

    return $distinct;
}
