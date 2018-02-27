<?php

include "globalvars.php";

/* ==================================================================
 * =                       INITIALIZATION                           =
 * ==================================================================
 */

/* Get database connection, or initialise it */
function  db_connection() {
    static $conn;
    if ($conn === NULL){ 

        // Use the global variables as defined in gloabalvars.php
        global $server, $user, $password, $database;

        $conn = mysqli_connect($server, $user, $password, $database);
        // $conn = mysqli_connect("localhost", "bank", "password","traderbot_db");
    }
    return $conn;
}

function create_tables($conn) {

    if (create_sectors($conn) && create_stocks($conn) && create_queries($conn) &&
        create_history($conn) && create_fav_stocks($conn) && create_fav_sectors($conn)) {
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
        sector_name     varchar(32),
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
        stock_name      varchar(32),
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
        notif_freq  integer,
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
        notif_freq  integer,
        FOREIGN KEY (sector_id) REFERENCES sectors(sector_id) ON DELETE CASCADE
    )";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo "Error creating table fav_sectors: " . $conn->error . "<br>";
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
        ('Electricity',                          '/indices/electricity'),
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
        ('Travel & Leisure',                    '/indices/travel---leisure')";

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

    $sql = "DROP TABLE fav_sectors, fav_stocks, history, queries, stocks, sectors";

    if ($conn->multi_query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

}

function drop_database($conn) {

    $sql = "DROP DATABASE " . $database;

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
    

/* Insert data into FAV_STOCKS table */
function insert_fav_stock($conn, $stock_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT stock_id,'" . $date . "'," . $freq . " FROM stocks WHERE stock_name = '" . $stock_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }
}

/* Insert data into FAV_SECTORS table */
function insert_fav_sector($conn, $sector_name, $freq) {

    $date = date("Y-m-d");
    $sql = "INSERT INTO fav_stocks (stock_id, date_added, notif_freq) SELECT sector_id,'" . $date . "'," . $freq . " FROM sectors WHERE sector_name = '" . $sector_name . "'";

    if ($conn->query($sql) === TRUE) {
        return 1;
    } else {
        echo $sql . "<br>Error executing query: " . $conn->error . "<br>";
        return 0;
    }
}

// ==================================================================
// =                           PROCESSING                           =
// ==================================================================

/* Return JSON object of all favourite stocks */
function get_fav_stocks($conn) {

    $sql = "SELECT stock_id,ticker_symbol,stock_name FROM stocks WHERE stock_id IN (SELECT stock_id FROM fav_stocks)";

    if (($res = $conn->query($sql)) !== TRUE) {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

    $arr;

    while ($row = $res->fetch_assoc()) {

        $arr[] = ['id' => $row["stock_id"], 'ticker' => $row["ticker_symbol"], 'name' => $row["stock_name"]];

    }

    $fav_stocks = json_encode($arr);
    return $fav_stocks;

}

/* Return JSON object of all favourite sectors */
function get_fav_sectors($conn) {

    $sql = "SELECT sector_id,sector_name FROM stocks WHERE sector_id IN (SELECT sector_id FROM fav_sectors)";

    if (($res = $conn->query($sql)) !== TRUE) {
        echo $sql . "<br>Error: " . $conn->error . "<br>";
        return 0;
    }

    $arr;

    while ($row = $res->fetch_assoc()) {

        $arr[] = ['id' => $row["sector_id"], 'name' => $row["sector_name"]];

    }

    $fav_stocks = json_encode($arr);
    return $fav_stocks;

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

// TODO: Make sure this runs all right, then fill in with INSERT/DELETEs
function update_faves($conn, $json_obj) {

    $fav_list = json_decode($json_obj, TRUE);

    foreach ($fav_list as $item) {
        foreach ($item as $entity) {
            echo $entity["id"] . ": " . $entity["fav"] . "\n";
        }
    }

}



function get_notifications() {

}
