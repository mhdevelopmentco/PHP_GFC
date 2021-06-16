<?php

require('lib/init.php');
include('templates/default/header.php');

$kind = "";
if (isset($_GET['action'])) {
    $kind = $_GET['action'];
}

if ($kind == "create_success") {
    //Subscription Success
    ?>

    <div class="container-fluid content">
        <div class="main-container">
            <div class="row">

                <div class="col-md-6 col-md-offset-3">
                    <div class="sub_result success_sub">
                        <h1>Success</h1>
                        <p>Congratulations! You have subscribed to the world’s most sophisticated building code
                            search
                            engine.</p>
                        <p>Let’s get started right away! Simply fill in your question and GoFetchCode will find the
                            right
                            answer in a snap. If you’re not sure where to start, take a look at our example
                            searches.</p>
                        <a class="btn pri_button" href="search.php">Start Your First Search</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } else if ($kind == "update_success") {
    //update subscription
    ?>

    <div class="container-fluid content">
        <div class="main-container">
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <div class="sub_result success_sub">
                        <h1>Success</h1>
                        <p>Congratulations! You have updated your subscription.</p>
                        <a class="btn pri_button" href="search.php">Continue To Search</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

<?php } else if ($kind == "create_failed" || $kind == "update_failed") {

    $failed = $_SESSION['fail_sub_count'];

    $fail_nth_str = "1st";

    if ($failed == 1) {
        $fail_nth_str = "1st";
    } else if ($failed == 2) {
        $fail_nth_str = "2nd";
    } else if ($failed == 3) {
        $fail_nth_str = "3rd";
    } else {
        $fail_nth_str = "too_many";
    }

    ?>

    <div class="container-fluid content">
        <div class="main-container">
            <div class="row">

                <div class="col-md-6 col-md-offset-3">

                    <div class="sub_result failed_sub">
                        <h1>Something went wrong</h1>

                        <?php if ($_SESSION['err_msg']) {
                            echo '<p>' . $_SESSION['err_msg'] . '</p>';
                            init_err_msg();
                        } ?>

                        <?php if ($fail_nth_str != "too_many") { ?>
                            <p>It was your <?php echo $fail_nth_str ?> try.</p>
                        <?php } ?>
                        <?php if ($failed > 3) { ?>
                            <p>It seems the problem hasn’t been solved. We’re very sorry – please contact our customer
                                service team and they’ll get you out of this pickle as soon as possible</p>
                        <?php } ?>
                        <p>We’re very sorry, but something must have gone wrong. At this moment we haven’t received your
                            payment and your credit card has not been charged.</p>
                        <p>We know it’s annoying, but please try again.</p>

                        <?php if ($kind == "update_failed") { ?>
                            <a class="btn pri_button" href="subscription_update.php">Try Again</a>
                        <?php } else { ?>
                            <a class="btn pri_button" href="subscription_create.php">Try Again</a>
                        <?php } ?>
                    </div>

                </div>

            </div>
        </div>
    </div>

<?php } ?>

<?php include('templates/default/footer.php'); ?>