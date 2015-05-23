<?php namespace Controller;

use Model\Factory;
use PXL\Core\Tools;

class Home extends BaseController
{

	public function indexAction() {

        $services = Factory\Services::getAll(true);
        $cases    = Factory\Cases::getShowCased();
        $blog     = Factory\Blog::getLatest(3);

        // photo's (instagram feed)


        $this->set('services', $services);
		$this->set('cases', $cases);
        $this->set('blog', $blog);
	}


    public function newsletterAction() {

         if (isset($_POST['newsletter']) && $_GET["send"] == "1") {

            $empty    = true;
            $response = array();

            foreach ($_POST as $key => $value) {
                if ($key == 'newsletter') continue;

                if ($key == 'email') {
                   if(filter_var($value, FILTER_VALIDATE_EMAIL) == true) {
                        $empty = false;
                   } else {
                       $response['errors'][] = array(
                        "element" => $key
                    );
                   }
                }

                if(!empty($value)){
                    $empty = false;
                }


                if( empty( $value ) ){
                    $response['errors'][] = array(
                        "element" => $key
                    );
                }

            }

            if ( ! count($response['errors'])){

                if ($_POST['newsletter'] === 'on') {

                    $contact = (Object) null;

                    $contact->email         = (string) pxl_db_safe($_POST['email']);
                    $contact->voornaam      = (string) pxl_db_safe($_POST['name']);

                    $ch = curl_init();

                    curl_setopt($ch, CURLOPT_URL,
                        "https://app.klantenbinder2.nl/api/contacts?token=265b71d0f3466efa1566591a24927194");
                    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact));
                    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                    $responseKb = curl_exec($ch);

                    curl_close($ch);
                }

                $response['result'] = true;

            } else {
                $response['result'] = false;
            }

            $this->set('response', $response);

         }
    }

	public function contactAction() {

        // why the _GET['send'] == 1 check?

        if (isset($_POST['contact'])) {

            $msg      = '';
            $empty    = true;
            $response = array();

            foreach ($_POST as $key => $value) {

                switch ($key) {

                    // may be empty (or irrelevant)
                    case 'contact':
                    case 'website':
                        continue 2;

                    case 'email':
                        if (filter_var($value, FILTER_VALIDATE_EMAIL) == true) {
                            $empty = false;
                        } else {
                            $response['errors'][$key] = true;
                        }
                        break;

                    case 'message':
                        if ( ! empty($value)) {
                            $empty = false;
                        } else {
                            $response['errors'][$key] = true;
                        }
                        break;
                }

                if ( ! empty($value)) {
                    $empty = false;
                    $msg  .= "<strong>" . ucfirst(str_replace("_", " ", $key)) . "</strong>:<br/>" . $value . "<br/><br/>";
                }


                if (empty($value)) {
                    $response['errors'][$key] = true;  //ML::label('contact form error empty ' . $key);
                    //pr($response['errors']);
                }

            }

            if ( ! count($response['errors'])) {

                // if ($_POST['newsletter'] === 'on') {

                //     $contact = (Object) null;

                //     $contact->email    = (string) pxl_db_safe($_POST['email']);
                //     $contact->voornaam = (string) pxl_db_safe($_POST['name']);

                //     $ch = curl_init();

                //     curl_setopt($ch, CURLOPT_URL,
                //         "https://app.klantenbinder2.nl/api/contacts?token=265b71d0f3466efa1566591a24927194");
                //     curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
                //     curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($contact));
                //     curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
                //     curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

                //     $responseKb = curl_exec($ch);

                //     curl_close($ch);
                // }

                $email = new Tools\Emailer();

                if (APPLICATION_ENV !== 'production') {
                    $email->addressee('coen@pixelindustries.com');
                } else {
                    $email->addressee('info@pixelindustries.com');
                }

                $email->sender('info@pixelindustries.com');
                $email->subject('Pixelindustries website contact');
                $email->messageHtml($msg);
                $email->send();

                $response['result'] = true;

            } else {
                $response['result'] = false;
            }

            $this->set('response', $response);
        }


	}
}