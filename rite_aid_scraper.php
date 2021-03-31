<?php

const RITE_AID_BK_IDS = [
    4864 => '101 Clinton Street, Brooklyn, NY 11201',
    3627 => '1154 Clarkson Avenue, Brooklyn, NY 11212',
    4738 => '1631-43 Pitkin Avenue, Brooklyn, NY 11212',
    3978 => '1679 Bedford Avenue, Brooklyn, NY 11225',
    3869 => '1791 Utica Avenue, Brooklyn, NY 11234',
    4733 => '182 Smith Street, Brooklyn, NY 11201',
    1881 => '185 Kings Highway, Brooklyn, NY 11223',
    4893 => '1950 Fulton Street, Brooklyn, NY 11233',
    3958 => '2002 Avenue U, Brooklyn, NY 11229',
    10585 => '2064 Mill Avenue, Brooklyn, NY 11234',
    4935 => '249 7th Avenue, Brooklyn, NY 11215',
    10575 => '2577 Nostrand Avenue, Brooklyn, NY 11210',
    4679 => '2819 Church Avenue, Brooklyn, NY 11226',
    10586 => '2981 Ocean Avenue, Brooklyn, NY 11235',
    4257 => '3001-27 Mermaid Avenue, Brooklyn, NY 11224',
    10584 => '320 Smith Street, Brooklyn, NY 11231',
    2612 => '344 Avenue X, Brooklyn, NY 11223',
    4876 => '3823 Nostrand Avenue, Brooklyn, NY 11235',
    3864 => '4102 Church Avenue, Brooklyn, NY 11203',
    3883 => '5102 13th Avenue, Brooklyn, NY 11219',
    543 => '5224 Fifth Avenue, Brooklyn, NY 11220',
    3766 => '5901 Bay Parkway, Brooklyn, NY 11204',
    4269 => '6201-23 Fourth Avenue, Brooklyn, NY 11220',
    10579 => '6423 Fort Hamilton Pkwy, Brooklyn, NY 11219',
    3879 => '6900 4th Avenue, Brooklyn, NY 11209',
    10574 => '7118 3rd Avenue, Brooklyn, NY 11209',
    10573 => '7501 5th Avenue, Brooklyn, NY 11209',
    4954 => '7812 Flatlands Avenue, Brooklyn, NY 11236',
    4782 => '783 Manhattan Avenue, Brooklyn, NY 11222',
    10577 => '8222 18th Avenue, Brooklyn, NY 11214',
    4246 => '892-908 Flatbush Avenue, Brooklyn, NY 11226',
    4712 => '9302 3rd Avenue, Brooklyn, NY 11209',
    1947 => '960 Halsey Street, Brooklyn, NY 11233',
    3888 => '9738 Sea View Avenue, Brooklyn, NY 11236',
];
const RITE_AID_MANHATTAN_IDS = [
    7759 => '1033 St Nicholas Avenue, New York, NY 10032',
    2727 => '1510 Saint Nicholas Avenue, New York, NY 10033',
    4215 => '1535 2nd Avenue, New York, NY 10075',
    4688 => '1849 2nd Avenue, New York, NY 10128',
    4196 => '188 9th Avenue, New York, NY 10011',
    10531 => '195 8th Avenue, New York, NY 10011',
    7767 => '1951 First Avenue, New York, NY 10029',
    4885 => '210-20 Amsterdam Avenue, New York, NY 10023',
    4798 => '2155 Third Avenue, New York, NY 10035',
    7760 => '2170 Frederick Douglass Blvd, New York, NY 10026',
    10534 => '225 Liberty Street, New York, NY 10281',
    4964 => '26 Grand Central Terminal, New York, NY 10017',
    4195 => '282 8th Avenue, New York, NY 10001',
    3110 => '301 West 50th Street, New York, NY 10019',
    4945 => '35-45 West 125th Street, New York, NY 10027',
    4185 => '3539 Broadway, New York, NY 10031',
    2010 => '4046 Broadway, New York, NY 10032',
    1711 => '408 Grand Street, New York, NY 10002',
    7808 => '4188 Broadway, New York, NY 10033',
    4887 => '4910 Broadway, New York, NY 10034',
    // 4261 => '501 6th Avenue, New York, NY 10011', doesnt let you sign up for this one?
    1225 => '534 Hudson Street, New York, NY 10014',
    4971 => '550 Second Avenue, New York, NY 10016',
    4202 => '7 Madison Street, New York, NY 10038',
    4189 => '741 Columbus Avenue, New York, NY 10025',
    4205 => '81 1st Avenue, New York, NY 10003',
    3771 => '85 Avenue D, New York, NY 10009',
];
const RIDE_AID_QUEENS_IDS = [
    4873 => '55-60 Myrtle Avenue Ridgewood, NY 11385',
    10605 => '583 Grandview Avenue Ridgewood, NY 11385',
    10568 => '21-25 Broadway Long Island City, NY 11106',
    4852 => '71-14 Austin Street Forest Hills, NY 11375',
    4858 => '46-12 Greenpoint Avenue Sunnyside, NY 11104',
    3865 => '218-35 Hempstead Avenue Queens Village, NY 11429',
];

require_once "vendor/autoload.php";
use Abraham\TwitterOAuth\TwitterOAuth;

function helpMessage($exit = 0) {
    echo <<<EOT
Usage: php rite_aid_vax.php > rite_aid_vax.log
    -a  twitter CONSUMER_KEY (required)
    -b  twitter CONSUMER_SECRET (required)
    -c  twitter ACCESS_TOKEN (required)
    -d  twitter ACCESS_TOKEN_SECRET (required)
    -e  email for notifications (optional)
    -i  slack channel without leading '#' (shell out to post to slack?) (optional)
    -j  slack host (optional)
    -k  slack port (optional)
EOT;

    exit($exit);
}

function getArgs($args) {
    if (
        !isset($args['a']) || !isset($args['b']) || !isset($args['c']) || !isset($args['d'])
    ) {
        helpMessage(1);
        return;
    }

    return [
        'twitter_consumer_key' => $args['a'],
        'twitter_consumer_secret' => $args['b'],
        'twitter_access_token' => $args['c'],
        'twitter_access_token_secret' => $args['d'],
        'email' => $args['e'] ?? null,
        'slack' => [
            'channel' => $args['i'] ?? null,
            'host' => $args['j'] ?? null,
            'port' => $args['k'] ?? null,
        ],
    ];
}

function getVaxData($id) {
    $ch = curl_init();

    // bust weird caching??
    $a = random_int(1, PHP_INT_MAX);
    $b = random_int(1, PHP_INT_MAX);

    curl_setopt($ch, CURLOPT_URL, "https://www.riteaid.com/services/ext/v2/vaccine/checkSlots?storeNumber=$id&$a=$b");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_TIMEOUT, 5); // timeout in seconds
    $output = curl_exec($ch);
    $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    if ($httpcode !== 200) {
        throw new Exception("Got http code: $httpcode response: $output");
    }
    curl_close($ch);

    $data = json_decode($output, true);
    if (!is_array($data)) {
        throw new Exception("json_decoded data was not an array for store id $id.");
    }
    if (!isset($data['Status'])) {
        throw new Exception("Missing 'Status' response key for store id $id.");
    }
    if ($data['Status'] !== 'SUCCESS') {
        throw new Exception("Got status: {$data['Status']} for store id $id.");
    }
    return $data;
}

function checkDataForAppointments($data) {
    if (!isset($data['Data'])) {
        throw new Exception("expected key 'Data' was missing from data array");
    }
    if (!isset($data['Data']['slots'])) {
        throw new Exception("expected key 'slots' was missing from data array");
    }

    if (!isset($data['Data']['slots'][1])) { // wtf is slot 1 vs 2?
        throw new Exception("key 'Data.slots.1' was missing.");
    }
    if (!isset($data['Data']['slots'][2])) { // wtf is slot 1 vs 2?
        throw new Exception("key 'Data.slots.2' was missing.");
    }
    return !!$data['Data']['slots'][1] || !!$data['Data']['slots'][2];
}

function doLog($msg) {
    $date = date(DATE_RFC2822, time());
    echo "[$date] $msg\n";
}

function notifyVaccineAvailability($locations_with_vaccines, $twitter_conn, $slack_args) {
    if (count($locations_with_vaccines) > 3) {
        $location_lines = implode("\n", $locations_with_vaccines);
        $gist_cmd = "echo '$location_lines' | /home/dleibovic/bin/gist --filename ritenyc";
        $output = null;
        $code = null;
        exec($gist_cmd, $output, $code);
        $output_text = trim(implode("\n", $output));
        if ($code !== 0) {
            throw new Exception("gist creation got exit code: $code and output: $output_text");
        }
        $msg = "Found rite aid vaccine at many locations. See all locations with availablity here: $output_text and sign up here: https://www.riteaid.com/covid-vaccine-apt";
    } else {
        $msg = "Found rite aid vaccine at these locations: " . implode('; ', $locations_with_vaccines) .
            " https://www.riteaid.com/covid-vaccine-apt";
    }
    doLog("called notifyVaccineAvailability for: $msg");
    slackNotify($msg, $slack_args);
    $status = "$msg id: " . time();
    $post_tweets = $twitter_conn->post("statuses/update", ["status" => $status]);
    if ($twitter_conn->getLastHttpCode() === 200) {
        // Tweet posted successfully
    } else {
        var_dump($post_tweets);
        throw new Exception("Error posting tweet!");
    }
}

function slackNotify($msg, $slack_args) {
    if ($slack_args['channel'] && $slack_args['host'] && $slack_args['port']) {
        $cmd = "echo '#{$slack_args['channel']} $msg' | nc {$slack_args['host']} {$slack_args['port']}";
        exec($cmd);
    }
}

function notifyAboutErrors($msg, $slack_args, $email) {
    slackNotify($msg, $slack_args);
    mail($email, "Getting vax bot errors!", $msg);
}

const ERROR_INTERVAL_LENGTH = 60 * 10; // 10 mins
function main() {
    $args = getopt("ha:b:c:d:e:i:j:k:");
    if (isset($args["h"])) {
        helpMessage();
    }
    $args = getArgs($args);

    $error_interval_start_time = time();
    $num_errors_in_interval = 0;

    $twitter_conn = new TwitterOAuth($args['twitter_consumer_key'], $args['twitter_consumer_secret'],
        $args['twitter_access_token'], $args['twitter_access_token_secret']);
    $last_locations_with_vaccines = [];
    while (true) {
        try {
            $locations_with_vaccines = [];
            foreach ((RITE_AID_BK_IDS + RITE_AID_MANHATTAN_IDS + RIDE_AID_QUEENS_IDS) as $id => $location) {
                $resp = getVaxData($id);
                if (checkDataForAppointments($resp)) {
                    $msg = "Found rite aid vaccine at $location.";
                    doLog($msg);
                    $locations_with_vaccines[] = $location;
                } else {
                    doLog("No rite aid vaccines at $location.");
                }
                usleep(20000); // 20ms
            }

            if ($locations_with_vaccines) {
                if ($last_locations_with_vaccines == $locations_with_vaccines) {
                    doLog("Skipping notifying about current set of locations because it is same as previous set");
                    $last_locations_with_vaccines = []; // we will post about them if they are the same next time.
                } else {
                    notifyVaccineAvailability(
                        $locations_with_vaccines,
                        $twitter_conn,
                        $args['slack']
                    );
                    $last_locations_with_vaccines = $locations_with_vaccines;
                }
            } else {
                $last_locations_with_vaccines = $locations_with_vaccines;
            }
        } catch (Exception $e) {
            $num_errors_in_interval++;
            doLog("Caught exception: $e. Num errors in interval: $num_errors_in_interval.");
        }

        if ($num_errors_in_interval >= 10) {
            $interval_length_mins = round(ERROR_INTERVAL_LENGTH / 60, 2);
            doLog("Got $num_errors_in_interval errors in past $interval_length_mins mins. Sleeping for 5 minutes...");
            notifyAboutErrors(
                "Getting lots of errors - updates may be delayed. Might need to check on the bot and fix something cc <@dasl> ! Last error: $e",
                $args['slack'],
                $args['email']
            );
            sleep(60 * 5);
        }
        if (time() > ($error_interval_start_time + ERROR_INTERVAL_LENGTH)) {
            $error_interval_start_time = time();
            $num_errors_in_interval = 0;
        }

        sleep(30);
    }
}

main();
