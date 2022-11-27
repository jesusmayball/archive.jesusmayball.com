<?php
class Layout
{
    static function htmlTop($title)
    {
        global $config;
?>
        <!DOCTYPE html>
        <html>

        <head>
            <link rel="shortcut icon" href="/tickets/favicon.ico" />
            <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
            <title><?php echo ($title ? $title . ' - ' : "") . $config['complete_event_date']; ?></title>
            <link rel="stylesheet" href="/tickets/css/bootstrap.min.css" />
            <link rel="stylesheet" href="/tickets/css/bootstrap-datetimepicker.min.css" />
            <link rel="stylesheet" href="/tickets/css/mayball.css" />
            <link rel="stylesheet" href="/tickets/css/multi-select.css" />
            <?php if (file_exists(dirname(__FILE__) . "/../css/third-party.css")) { ?>
                <link rel="stylesheet" href="/tickets/css/third-party.css" />
            <?php } ?>
            <script src="/tickets/js/jquery-1.11.2.min.js" type="text/javascript"></script>
            <script src="/tickets/js/moment.min.js" type="text/javascript"></script>
            <script src="/tickets/js/jquery.multi-select.js" type="text/javascript"></script>
            <script src="/tickets/js/jquery.AjaxTable.js" type="text/javascript"></script>
            <script src="/tickets/js/bootstrap.min.js" type="text/javascript"></script>
            <script src="/tickets/js/bootstrap-datetimepicker.min.js" type="text/javascript"></script>
            <script src="/tickets/js/en-gb.js" type="text/javascript"></script>
            <script type="text/javascript">
                $("document").ready(function() {
                    $('#credits').modal({
                        show: false
                    });
                    $('#credit-link').click(function() {
                        $('#credits').modal("show");
                    });
                });
            </script>
        </head>

        <body>
        <?php
    }

    static function htmlBottom($includeFooter = true, $includeCredits = false)
    {
        global $config;
        ?>
            <div class="modal fade" id="credits" style="display: none">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                            <h3 class="modal-title">The Jesus Ticketing System</h3>
                        </div>
                        <div class="modal-body">
                            <p>Hi there, and thanks for using the Jesus Ticketing System.</p>
                            <p>We really hope it works out for you, if not, please get in contact with us and we'll try and improve it!</p>
                            <p>This system has seen a lot of changes since it's creation on December 7th 2008 (where it was a single html file containing the word "hello") thanks to the following people:</p>
                            <h4>Key Contributers</h4>
                            <ul>
                                <li>Rafi Levy - <i>2021 - Present</i></li>
                                <li>Peter Cowan - <i>2010 - </i></li>
                                <li>James Grant - <i>2011 - 2012</i></li>
                                <li>James Hodgson - <i>2009 - 2010</i></li>
                                <li>Rob Duncan - <i>2008 - 2009</i></li>
                            </ul>
                            <h4>Honourable Mentions</h4>
                            <ul>
                                <li>Jeremy Minton</li>
                            </ul>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Global site tag (gtag.js) - Google Analytics -->
            <script async src="https://www.googletagmanager.com/gtag/js?id=UA-114650639-1"></script>
            <script>
                window.dataLayer = window.dataLayer || [];

                function gtag() {
                    dataLayer.push(arguments);
                }
                gtag('js', new Date());

                gtag('config', 'UA-114650639-1');
            </script>
            <?php
            if ($includeFooter) {
                echo '<footer class="footer"><div class="container"><p class="text-muted">';
                echo 'Powered by the Jesus Ticketing System';
                if ($includeCredits) {
                    echo ' (version ' . VERSION . ')';
                }
                if (strtolower($config["college"]) !== "jesus college") {
                    echo " on behalf of " . $config["college"] . " " . $config["event"];
                }
                if ($includeCredits) {
                    echo " - <a id=\"credit-link\">Credits</a>";
                }
                echo '</p></div></footer>', PHP_EOL;
            }
            echo '</body>', PHP_EOL;
            echo '</html>', PHP_EOL;
        }

        static function htmlExit($title, $message)
        {
            global $config;
            Layout::htmlTop($title);
            ?>
            <div class="container text-center purchase-container">
                <div class="page-header">
                    <h2><?php echo $config['complete_event_date']; ?></h2>
                </div>
                <div>
                    <p><?php echo $message; ?></p>
                </div>
            </div>
        <?php
            Layout::htmlBottom();
            exit();
        }

        static function echoActive($section, $searchTerm)
        {
            if ($section == $searchTerm) {
                echo "active";
            }
        }

        static function htmlAdminTop($title, $section = "home")
        {
            global $config;
            Layout::htmlTop($title);

            $vulnerabilities = Vulnerabilities::healthcheck();
            if ($vulnerabilities) {
                echo "<div class=\"container text-center purchase-container\">";
                echo "<div class=\"page-header\">";
                echo "<h1>Unable to proceed</h1>";
                echo "</div>";
                echo "<ul>";
                foreach ($vulnerabilities as $vulnerability) {
                    echo "<li>" . $vulnerability . "</li>";
                }
                echo "</ul>";
                echo "</div>";
                Layout::htmlBottom();
                exit();
            }
        ?>
            <nav class="navbar navbar-default navbar-inverse navbar-static-top">
                <div class="container">
                    <div class="navbar-header">
                        <button aria-controls="navbar" aria-expanded="false" data-target="#navbar" data-toggle="collapse" class="navbar-toggle collapsed" type="button">
                            <span class="sr-only">Toggle navigation</span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                            <span class="icon-bar"></span>
                        </button>
                        <a href="index.php" class="navbar-brand"><?php echo $config['complete_event']; ?> Administration</a>
                    </div>
                    <div class="navbar-collapse collapse" id="navbar">
                        <ul class="nav navbar-nav">
                            <li class="<?php Layout::echoActive($section, "home"); ?>"><a href="index.php">Overview</a></li>
                            <li class="dropdown <?php Layout::echoActive($section, "tickets"); ?>" id="applications_menu">
                                <a class="dropdown-toggle" aria-expanded="false" role="button" data-toggle="dropdown" href="#">Applications and Tickets <span class="caret"></span></a>
                                <ul role="menu" class="dropdown-menu">
                                    <li><a href="newApplication.php"><span class="glyphicon glyphicon-plus">&nbsp;</span>New Application</a></li>
                                    <li><a href="newTicket.php"><span class="glyphicon glyphicon-plus">&nbsp;</span>New Ticket</a></li>
                                    <li><a href="tickets.php"><span class="glyphicon glyphicon-tags">&nbsp;</span>Manage Applications and Tickets</a></li>
                                    <li><a href="waitingList.php"><span class="glyphicon glyphicon-list">&nbsp;</span>Waiting List</a></li>
                                    <li class="divider"></li>
                                    <li><a href="ticketClasses.php"><span class="glyphicon glyphicon-tags">&nbsp;</span>Ticket Classes and Prices</a></li>
                                    <li class="divider"></li>
                                    <li><a href="paymentCheckin.php"><span class="glyphicon glyphicon-check">&nbsp;</span>Check-in Ticket Payment</a></li>
                                    <li><a href="payments.php"><span class="glyphicon glyphicon-list">&nbsp;</span>View Ticket Payments</a></li>
                                    <li><a href="nameChangeCheckin.php"><span class="glyphicon glyphicon-check">&nbsp;</span>Check-in Name Change Payment</a></li>
                                    <li><a href="nameChanges.php"><span class="glyphicon glyphicon-list">&nbsp;</span>View Name Changes</a></li>
                                    <li><a href="paymentProcess.php"><span class="glyphicon glyphicon-cloud-upload">&nbsp;</span>Auto Check-in Payments</a></li>
                                    <li class="divider"></li>
                                    <li><a href="receiptsDownload.php"><span class="glyphicon glyphicon-save">&nbsp;</span>Download Receipts</a></li>
                                    <li class="divider"></li>
                                    <li><a href="generateETickets.php"><span class="glyphicon glyphicon-barcode">&nbsp;</span>E-Tickets</a></li>
                                    <li><a href="prepareTickets.php"><span class="glyphicon glyphicon-barcode">&nbsp;</span>Prepare Tickets</a></li>
                                    <li><a href="prepareTicketEntry.php"><span class="glyphicon glyphicon-barcode">&nbsp;</span>Prepare Ticket Entry</a></li>
                                </ul>
                            </li>
                            <li class="dropdown <?php Layout::echoActive($section, "emails"); ?>">
                                <a class="dropdown-toggle" aria-expanded="false" role="button" data-toggle="dropdown" href="#">Emails <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="emails.php"><span class="glyphicon glyphicon-pencil">&nbsp;</span>Manage Emails</a></li>
                                    <li><a href="massEmailer.php"><span class="glyphicon glyphicon-envelope">&nbsp;</span>Mass Emailer</a></li>
                                </ul>
                            </li>
                            <li class="dropdown <?php Layout::echoActive($section, "settings"); ?>">
                                <a class="dropdown-toggle" aria-expanded="false" role="button" data-toggle="dropdown" href="#">Settings <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="globalSettings.php"><span class=" glyphicon glyphicon-cog">&nbsp;</span>Global Settings</a></li>
                                    <li><a href="permissions.php"><span class=" glyphicon glyphicon-lock">&nbsp;</span>Permissions</a></li>
                                    <li class="divider"></li>
                                    <li><a href="tc.php"><span class="glyphicon glyphicon-align-left">&nbsp;</span>Terms &amp; Conditions</a></li>
                                    <li><a href="logo.php"><span class="glyphicon glyphicon-picture">&nbsp;</span>Logo</a></li>
                                    <li><a href="welcome.php"><span class="glyphicon glyphicon-home">&nbsp;</span>Welcome</a></li>
                                    <li><a href="charities.php"><span class="glyphicon glyphicon-gift">&nbsp;</span>Charities</a></li>
                                </ul>
                            </li>
                            <li class="dropdown">
                                <a class="dropdown-toggle" aria-expanded="false" role="button" data-toggle="dropdown" href="#">Quick Links <span class="caret"></span></a>
                                <ul class="dropdown-menu">
                                    <li><a href="<?php echo $config['event_website']; ?>"><span class="glyphicon glyphicon-home">&nbsp;</span>Event website</a></li>
                                    <li><a href="../waitinglist.php"><span class="glyphicon glyphicon-list">&nbsp;</span>Waiting list (May need to re-log in)</a></li>
                                    <li><a href="../namechange.php"><span class="glyphicon glyphicon-transfer">&nbsp;</span>Name changes</a></li>
                                    <li class="divider"></li>
                                    <li><a href="../reserve.php?mode=alumni&amp;r=<?php echo $config['alumni_key']; ?>"><span class="glyphicon glyphicon-tag">&nbsp;</span>Alumni Ticket Reservation</a></li>
                                    <li><a href="../reserve.php?mode=agent"><span class="glyphicon glyphicon-tag">&nbsp;</span>Agent Ticket Reservation (May need to re-log in)</a></li>
                                    <li><a href="../reserve.php?mode=uni"><span class="glyphicon glyphicon-tag">&nbsp;</span>Uni Ticket Reservation (May need to re-log in)</a></li>
                                    <li class="divider"></li>
                                    <li><a href="../entry/"><span class="glyphicon glyphicon-user">&nbsp;</span>Ball Entry</a></li>
                                </ul>
                            </li>
                        </ul>
                        <ul class="nav navbar-nav navbar-right">
                            <li><a href="../logout.php">Logout</a></li>
                        </ul>
                        <p class="navbar-text navbar-right">
                            <?php echo Layout::howLongLeft(); ?>
                        </p>
                    </div>
                </div>
            </nav>
            <?php
        }

        static function htmlAdminBottom($class)
        {
            Layout::htmlBottom(true, true);
        }

        static function howLongLeft()
        {
            global $config;
            $ballNight = $config['dates']['ballnight'];
            $diff = $ballNight - time();
            if ($diff < 0) {
                if ($diff > (-12 * 60 * 60)) {
                    return $config['event'] . " time!";
                } else {
                    return "";
                }
            } else {
                if ($diff < 24 * 60 * 60) {
                    $hours = floor($diff / (60 * 60));
                    $minutes = floor(($diff - ($hours * 60 * 60)) / 60);
                    $retString = "";
                    if ($hours == 1) {
                        $retString .= $hours . " hour ";
                    } else if ($hours != 0) {
                        $retString .= $hours . " hours ";
                    }

                    if ($minutes == 1) {
                        $retString .= $minutes . " minute";
                    } else if ($minutes == 0) {
                        if ($hours == 0) {
                            $retString .= "Less than a minute";
                        }
                    } else {
                        $retString .= $minutes . " minutes";
                    }
                    return $retString . " to go";
                } else {
                    $days = floor($diff / (24 * 60 * 60));
                    if ($days == 1) {
                        return $days . " day to go";
                    } else {
                        return $days . " days to go";
                    }
                }
            }
        }

        static function ticketTypeOptions($ticketTypes)
        {
            global $config;
            echo '<option value="">Choose a ticket type...</option>';
            foreach ($ticketTypes as $id => $type) {
                echo '<option value="' . $id . '">' . $type->ticket_type . '</option>';
            }
        }

        static function collegesOptions($allColleges, $includeAlumni)
        {
            global $config;
            echo '<option value="" selected="selected">Choose a college...</option>';
            if ($includeAlumni) {
                echo '<option value="' . $config["college"] . ' Alumni">' . $config["college"] . ' Alumni</option>';
            }
            if ($allColleges) {
            ?>
                <option value="None">Non-University</option>
                <option value="Christs College">Christs College</option>
                <option value="Churchill College">Churchill College</option>
                <option value="Clare College">Clare College</option>
                <option value="Clare Hall">Clare Hall</option>
                <option value="Corpus Christi College">Corpus Christi College</option>
                <option value="Darwin College">Darwin College</option>
                <option value="Downing College">Downing College</option>
                <option value="Emmanuel College">Emmanuel College</option>
                <option value="Fitzwilliam College">Fitzwilliam College</option>
                <option value="Girton College">Girton College</option>
                <option value="Gonville and Caius College">Gonville and Caius College</option>
                <option value="Homerton College">Homerton College</option>
                <option value="Hughes Hall">Hughes Hall</option>
                <option value="Jesus College">Jesus College</option>
                <option value="Kings College">Kings College</option>
                <option value="Lucy Cavendish College">Lucy Cavendish College</option>
                <option value="Magdalene College">Magdalene College</option>
                <option value="Murray Edwards College">Murray Edwards College</option>
                <option value="Newnham College">Newnham College</option>
                <option value="Pembroke College">Pembroke College</option>
                <option value="Peterhouse College">Peterhouse College</option>
                <option value="Queens College">Queens College</option>
                <option value="Robinson College">Robinson College</option>
                <option value="St Catharines College">St Catharines College</option>
                <option value="St Edmund's College">St Edmunds College</option>
                <option value="St Johns College">St Johns College</option>
                <option value="Selwyn College">Selwyn College</option>
                <option value="Sidney Sussex College">Sidney Sussex College</option>
                <option value="Trinity College">Trinity College</option>
                <option value="Trinity Hall">Trinity Hall</option>
                <option value="Wolfson College">Wolfson College</option>
    <?php
            }
        }

        static function titlesOptions($itsEaster)
        {
            echo '<option value="">Choose...</option>';
            echo '<option value="Mr">Mr</option>';
            echo '<option value="Mrs">Mrs</option>';
            echo '<option value="Miss">Miss</option>';
            echo '<option value="Ms">Ms</option>';
            echo '<option value="Mx">Mx</option>';
            echo '<option value="Dr.">Doctor</option>';
            echo '<option value="Prof.">Professor</option>';
            echo '<option value="Sir">Sir</option>';

            if ($itsEaster) {
                echo '<option value="Admiral">Admiral</option>';
                echo '<option value="Captain">Captain</option>';
                echo '<option value="Comrade">Comrade</option>';
            } else {
                echo '<!--<option value="Admiral">Admiral</option>-->';
                echo '<!--<option value="Captain">Captain</option>-->';
                echo '<!--<option value="Comrade">Comrade</option>-->';
            }
        }

        static function dietOptions()
        {
            echo '<option value="None">None</option>';
            echo '<option value="Vegetarian">Vegetarian</option>';
            echo '<option value="Vegan">Vegan</option>';
            echo '<option value="Pescatarian">Pescatarian</option>';
            echo '<option value="Gluten Free (NF)">Gluten Free</option>';
            echo '<option value="Nut Free (NF)">Nut Free</option>';
            echo '<option value="Kosher">Kosher</option>';
            echo '<option value="Halal">Halal</option>';
            echo '<option value="Vegetarian + NF">Vegetarian + NF</option>';
            echo '<option value="Vegetarian + GF">Vegetarian + GF</option>';
            echo '<option value="Vegan + NF">Vegan + NF</option>';
            echo '<option value="Vegan + GF">Vegan + GF</option>';
            echo '<option value="Pescatarian + NF">Pescatarian + NF</option>';
            echo '<option value="Pescatarian + GF">Pescatarian + GF</option>';
            echo '<option value="Halal + NF">Halal + NF</option>';
            echo '<option value="Halal + GF">Halal + GF</option>';
            echo '<option value="Kosher + NF">Kosher + NF</option>';
            echo '<option value="Kosher + GF">Kosher + GF</option>';
            echo '<option value="Kosher + Vegetarian">Kosher + Vegetarian</option>';
            echo '<option value="Kosher + Vegan">Kosher + Vegan</option>';
        }

        static function getLogoURL()
        {
            foreach (scandir(dirname(__FILE__) . "/../res/") as $file) {
                if (preg_match("/logo\-[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}\.png/", $file)) {
                    return $file;
                }
            }
            //Fall back
            return "logo.jpg";
        }
    }
    ?>