<div id="ct-ultimate-gdpr-cookies-scanner">
    <div class="ct-ultimate-gdpr-cookies-scanner-content">
        <div class="row">
            <div class="col-md-12">
                <div class="ct-ultimate-gdpr-cookies-scanner-wrapper">
                    <div class="ct-ultimate-gdpr-cookies-scanner__Close">
                        <span class="fa fa-times fa-2" aria-hidden="true"></span>
                        <span class="sr-only"><?php echo esc_html__('Close', 'ct-ultimate-gdpr'); ?></span>
                    </div>
                    <h2 class="text-center"><?php echo esc_html__('Cookies scanner', 'ct-ultimate-gdpr'); ?></h2>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__details text-center cookies-scanner-content__details-top">
                        <p>
                            <?php echo esc_html__('Our busy bees are searching for cookies on:', 'ct-ultimate-gdpr'); ?>
                            <br>
                            <span class="ct-ultimate-gdpr-cookies-scanner__Notice ct-ultimate-gdpr-cookies-scanner__Notice--Bold ct-ultimate-gdpr-cookies-scanner-content__Sites">
                                <span class="ct-ultimate-gdpr-cookies-scanner__Pages"></span>
                                <?php echo esc_html__('posts and pages', 'ct-ultimate-gdpr'); ?>
                            </span>
                            <?php echo esc_html__(' of your WordPress site.', 'ct-ultimate-gdpr'); ?>
                        </p>
                    </div>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__progress ct-ultimate-gdpr__ProgressBar text-center">
                        <div class="ct-ultimate-gdpr__ProgressBee text-center">

                        </div>
                    </div>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__details text-center cookies-scanner-content__details-bottom">
                        <p class="cookies-scanner-content__response-notice-0"><?php echo esc_html__('It can take up to few minutes. Thanks for your patience!', 'ct-ultimate-gdpr'); ?></p>
                        <p class="cookies-scanner-content__response-notice-1 hidden"><?php echo esc_html__('One of our bees returned with: ', 'ct-ultimate-gdpr'); ?></p>
                        <p><em class="cookies-scanner-content__response"></em></p>
                        <p class="cookies-scanner-content__response-notice-2 hidden">
                            <span class="cookies-scanner-content__response-notice-continue hidden"><?php echo esc_html__('Other bees are still searching...', 'ct-ultimate-gdpr'); ?></span>
                            <span class="cookies-scanner-content__response-notice-ended hidden"><?php echo esc_html__('All our busy bees are back with these results: ', 'ct-ultimate-gdpr'); ?></span>
                        </p>
                    </div>
                </div>
            </div>
            <div class="col-md-12">
                <div class="ct-ultimate-gdpr-cookies-scanner__right">
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__scan current">
                        <span class="pull-left ct-ultimate-gdpr-cookies-scanner__Message"><?php echo esc_html__('We are currently scanning:', 'ct-ultimate-gdpr'); ?></span>
                        <span class="pull-right ct-ultimate-gdpr-cookies-scanner__Notice"><span class="ct-ultimate-gdpr-cookies-scanner-content__scanned">0</span>/<span class="ct-ultimate-gdpr-cookies-scanner-content__scanTotal">0</span> URL</span>
                    </div>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__scan failed hidden">
                        <span class="pull-left ct-ultimate-gdpr-cookies-scanner__Message">
                            <?php echo esc_html__('Failed scans:', 'ct-ultimate-gdpr'); ?>
                            <span class="ct-ultimate-gdpr-cookies-scanner__Notice">
                                <span class="ct-ultimate-gdpr-cookies-scanner-content__failed">0</span>/<span class="ct-ultimate-gdpr-cookies-scanner-content__scanTotal">0</span>
                                URL
                            </span>
                            <a class="ct-ultimate-gdpr-cookies-scanner-content__show-failed hidden" href="#"><small><?php echo esc_html__('Show failed scans', 'ct-ultimate-gdpr'); ?><i class="bi bi-arrow-right-circle"></i></small></a>
                            <a class="ct-ultimate-gdpr-cookies-scanner-content__retry hidden" href="#"><small><?php echo esc_html__('Retry failed scans', 'ct-ultimate-gdpr'); ?><i class="bi bi-arrow-right-circle"></i></small></a>
                        </span>
                    </div>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__scan failed-urls hidden">
                        <div id="ct-ultimate-gdpr-cookies-scanner-content__retry-message" class="hidden"><?php echo esc_html__('Are you sure you want to retry failed scans?', 'ct-ultimate-gdpr'); ?></div>
                        <div class="pull-left urls ct-clearfix">
                            <ul class="ct-ultimate-gdpr-cookies-scanner-content__failed"></ul>
                        </div>
                    </div>
                    <div class="ct-ultimate-gdpr-cookies-scanner-content__url">
                        <span class="pull-left ct-ultimate-gdpr-cookies-scanner__Message">
                            <?php echo esc_html__('Currently scanned URL:', 'ct-ultimate-gdpr'); ?>
                        </span>
                        <span class="pull-right ct-ultimate-gdpr-cookies-scanner__Notice ct-ultimate-gdpr-cookies-scanner-content__currentUrl"></span>
                    </div>
                    <div class="ct-clearfix"></div>
                </div>
            </div>
        </div>
    </div>
</div>