<?php
    namespace Controller;

    use Model\Factory;

    class Cron extends BaseController {

    	/**
    	 * Update team member feed data
    	 *
    	 * @param  int $member_id 	if just updating a single member's feed
    	 * @return void
    	 */
    	public function updatesocialfeedsAction() {

    		$factory = Factory\SocialFeeds::getInstance();

    		$factory->updateInstagram($member_id);

            die('Done updating');

    	}

    }