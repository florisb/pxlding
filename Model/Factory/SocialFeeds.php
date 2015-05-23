<?php namespace Model\Factory;

class SocialFeeds extends BaseFactory {

    const INSTAGRAM_USER_ID   = '761578319';    // for user Pixelindustries
    const FEED_TYPE_INSTAGRAM = 'instagram';

	// note: this is the instagram token that is used for Kaasvkerels, but it
	// works fine for now. Also, you need to install the app to even get an instagram
	// account, so bollocks to that.
	const TOKEN_INSTAGRAM		 = '1543824059.f48f8e2.810a7552732c4919b5a50710531c8f55';



	/**
	 * Get all social media feeds combined into one, excluding video feeds.
	 *
	 * Feeds are sorted by date DESC
	 *
	 * @param  int	  $member_id
	 * @param  boolean  $maxItemCount   limits the items per itemtype
	 * @return array	[date] => array with items
	 */
	public function getFeed($maxItemCount = null)
    {
        $maxItemCount = (int) $maxItemCount;
        $feed         = array();

		$instagramPosts = self::getInstagram();

		foreach ($instagramPosts as $instagram)
        {
			$entry = $instagram->toAssocArray();

			$feed[$entry['date']][self::FEED_TYPE_INSTAGRAM] = $entry;
		}

		// sort by date
		if (count($feed))
        {
			krsort($feed);
		}

		if (!empty($maxItemCount))
        {
            $limitedFeed = array();
            $i           = 0;

			foreach ($feed as $date => $feeditem)
            {
				foreach ($feeditem as $type => $item)
                {
					if ($i >= $maxItemCount) break 2;

					$limitedFeed[$date][$type] = $item;

					$i++;
				}
			}
		}

		return $limitedFeed;
	}


	/* INSTAGRAM */

	/**
	 * Gets all instagram posts
	 *
	 * @param  int $member_id
	 * @return map
	 */
	public static function getInstagram()
    {
		$q = "
			SELECT
				*
			FROM
				`cms_m17_instagram`
		";

		$stmt = self::stmt($q);

		return self::db()->matrix($stmt);
	}

	/**
	 * Updates instagram feed
	 *
	 * @return void
	 */
	public function updateInstagram()
    {
		$instagram_feed = file_get_contents("https://api.instagram.com/v1/users/" . self::INSTAGRAM_USER_ID . "/media/recent?access_token=" . self::TOKEN_INSTAGRAM);
		$instagram_feed = json_decode($instagram_feed, true);

		foreach ($instagram_feed['data'] as $instagram)
        {
			$data = array(
                'post_id'     => $instagram['id'],
                'title'       => utf8_encode($instagram['caption']['text']),
                'image'       => $instagram['images']['low_resolution']['url'],
                'date'        => $instagram['caption']['created_time'],
                'likes'       => $instagram['likes']['count'],
                'comments'    => $instagram['comments']['count'],
                'url'         => $instagram['link'],
			);

            if (empty($data['date'])) {
                $data['date'] = '';
            }

			// check if post_id already exists
			$exists = $this->checkInstagramExists($instagram['id']);

            if ( ! $exists && ! empty($data['image']))
            {
            	// attempt to get larger version of image
            	$image = $this->getLargerInstagramImage($data['post_id']);

            	if ( ! empty($image)) {
            		$data['image'] = $image;
            	}

                $newPost = new \Model\Entity\InstagramPost($data);
                $newPost->save();
			}
		}
	}

	/**
	 * Attempts to retrieve larger version of the image for a given
	 * instagram 'post' ID.
	 *
	 * @param  int $postId
	 * @return string
	 */
	public function getLargerInstagramImage($postId)
	{
		$instagram_feed = file_get_contents("https://api.instagram.com/v1/media/" . $postId . "?access_token=" . self::TOKEN_INSTAGRAM);
		$instagram_feed = json_decode($instagram_feed, true);

		if ( ! isset($instagram_feed['data']['images']['standard_resolution']['url'])) {
			return null;
		}

		return $instagram_feed['data']['images']['standard_resolution']['url'];
	}

	/**
	 * Checks whether an instagram entry exists with a given post ID
	 *
	 * @param  string $postID
	 * @return boolean
	 */
	public function checkInstagramExists($postID)
    {
		$q = "
			SELECT
				COUNT(*) AS total
			FROM
				`cms_m17_instagram`
			WHERE
				`post_id` = :post_id
		";

		$stmt = $this->stmt($q, array(
			':post_id' => array($postID, 's')
		));

        // $result = self::db()->row($stmt, 'Model\Entity\InstagramPost');
        $result = self::db()->row($stmt);

		return ! (empty($result) || !$result->total );
	}



	/**
	 * Retrieve URL through cURL GET
	 *
	 * @param  string $url
	 * @param  array  $params
	 * @return string
	 */
	public function getUrl($url, $params = array())
    {

		if (!empty($params))
        {
			$url .= '?' . http_build_query($params, null, '&');
		}

		$ch = curl_init();

		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		// curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params, null, '&'));

        $ret = curl_exec($ch);

        curl_close($ch);

		return $ret;
	}

	/**
	 * Create a debug log entry
	 */
	public static function debugLog($message)
    {
		$file = __DIR__ . '/../../__cron_socialfeeds_debug.log';

        file_put_contents($file, '[' . date('Y-m-d H:i:s') . ']  ' . $message . "\n", FILE_APPEND);
	}
}