<?php

namespace App\Services;

use App\Models\Runner;
use App\Models\Captain;
use App\Models\Reservation;
use App\Models\Race;
use App\Models\Organizer;
use App\Models\Translation;
use App\Services\ExportService;
use App\Jobs\MailJob;

class MailService
{
    private $exportService;

    public function __construct(ExportService $exportService) 
    {   
        $this->exportService = $exportService;
    }  

    public function sendEmail($recipient, $data, $subject, $templateType, $template, $fromName = 'NoReply', $pdf = null)
    {
        $params['data']           = $data;
        $params['recipient']      = $recipient;
        $params['templateType']   = $templateType;
        $params['template']       = $template;
        $params['subject']        = $subject;
        $params['pdf']            = $pdf;
        $params['fromEmail']      = env('MAIL_FROM_ADDRESS');
        $params['fromName']       = env('MAIL_FROM_NAME');
        
        dispatch(new MailJob($params));
    }

    public function sendRunnerRegistrationConfirmation(Runner $runner, $reservationId)
    {
        $reservation = Reservation::find($reservationId);
        $captain = Captain::find($reservation->captain_id);
        $race = Race::find($reservation->race_id);
        $data['runner'] = $runner;
        $data['captain'] = $captain;
        $data['race'] = $race;
        $data['reservation'] = $reservation;
        $template =  $this->getRightTemplate($data['captain']->organizer_id, 'runner-registration-runner-confirmation');
        $encode = json_decode($template->content);
        $data['registred'] = $encode->registred;
        $data['name'] = $encode->name;
        $data['captain1'] = $encode->captain1;

        $this->sendEmail($data['runner']->email, $data, __('Runner registration successful'), 'view', 'mail.runner-registration-runner-confirmation', 'Business Run');
 
        //$this->sendEmail('denis@blue-bear.hr', $data, 'Registracija trkača uspešna', 'view', 'mail.runner-registration-runner-confirmation', 'Business Run');

        $template =  $this->getRightTemplate($data['captain']->organizer_id, 'runner-registration-captain-confirmation');
        $encode = json_decode($template->content);
        $data['registred'] = $encode->registred;
        $data['reservation1'] = $encode->reservation1;
        $data['race1'] = $encode->race1;

        $this->sendEmail($data['captain']->email, $data, __('Runner registration successful'), 'view', 'mail.runner-registration-captain-confirmation', 'Business Run');
        
        //$this->sendEmail('denis@blue-bear.hr', $data, 'Registracija trkača uspešna', 'view', 'mail.runner-registration-captain-confirmation', 'Business Run');
    }

    public function sendPrebillEmail(Reservation $reservation) 
    {
        $data = [];
        $captain = Captain::find($reservation->captain_id);
        $race = Race::find($reservation->race_id);
        $title = __('Business Run: predračun za rezervaciju'). ' #' . $reservation->id . ' '. __('for the race') .' ' . $race->name;       
        $template = 'mail.empty';

        $pdf =  $this->exportService->exportToPdf($reservation, 'invoice', true);

        $this->sendEmail($captain->email, $data, $title, 'view', $template, 'Business Run', $pdf);

        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', $template, 'Business Run', $pdf);
    }

    public function sendCaptainRegistrationConfirmation(Captain $captain)
    {
        $data['captain'] = $captain;
        $data['organizer'] = Organizer::find($captain->organizer_id);        
        $template = $this->getRightTemplate($data['organizer']->id, 'captain-registration-captain-confirmation' );
        $encode = json_decode($template->content);
       
        $this->sendEmail($captain->email, $data, $captain->company_name .' '. __('welcome to Business Run'), 'view', 'mail.captain-registration-captain-confirmation', 'Business Run');

        //$this->sendEmail('denis@blue-bear.hr', $data, $captain->company_name .' doborodošli na Serbia Business Run', 'view', 'mail.captain-registration-captain-confirmation', 'Business Run');

        $template1 = $this->getRightTemplate($data['organizer']->id, 'captain-registration-organizer-confirmation');
        $encode1 = json_decode($template1->content);

        $this->sendEmail($data['organizer']->email, $data, $captain->company_name .' '. __('welcome to Business Run'), 'view', 'mail.captain-registration-organizer-confirmation', 'Business Run');
    
        //$this->sendEmail('denis@blue-bear.hr', $data, $captain->company_name .' doborodošli na Serbia Business Run', 'view', 'mail.captain-registration-organizer-confirmation', 'Business Run');
    }

    public function sendPaymentChangeNotice(Reservation $reservation)
    { 
        $data['reservation'] = $reservation;
        $data['captain'] = Captain::find($reservation->captain_id);
        $data['organizer'] = Organizer::find($data['captain']->organizer_id);
        $data['race'] = Race::find($reservation->race_id);
        $title = __('Business Run: Recorded payment for reservation'). ' #' . $reservation->id . ' '. __('for the race') .' ' . $data['race']->name;
        $template = $this->getRightTemplate($data['organizer']->id, 'send-payment-change-captain-notice');
        $encode = json_decode($template->content);
        $data['forRace'] = $encode->forRace;
        $data['respect'] = $encode->respect;
        $data['likeCaptain'] = $encode->likeCaptain;
        $data['notice'] = $encode->notice;
        $data['paymentConfirmed'] = $encode->paymentConfirmed;
        $data['downloadBill'] = $encode->downloadBill;
        $data['sbr'] = $encode->sbr;
        $data['goodLuck'] = $encode->goodLuck;
        
        if($reservation->race->organizer_id == 2)
        {
            $pdf = $this->exportService->exportToPdf($reservation, 'bill', true);
        }
        else
        {
            $pdf = $this->exportService->exportToPdf($reservation, 'bill-old', true);
        }

        $this->sendEmail($data['captain']->email, $data, $title, 'view', 'mail.send-payment-change-captain-notice', 'Business Run', $pdf);
    
        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', 'mail.send-payment-change-captain-notice', 'Business Run', $pdf);
    }

    public function sendReservationCreatedCaptainNotice(Reservation $reservation)
    {
        $data['captain'] = Captain::find($reservation->captain_id);
        $data['organizer'] = Organizer::find($data['captain']->organizer_id);
        $data['race'] = Race::find($reservation->race_id);
        $data['reservation'] = $reservation;
        $title = __('Business Run: You have made a new reservation'). ' #' . $reservation->id . ' '. __('for the race') .' ' . $data['race']->name;        
        $template = $this->getRightTemplate($data['organizer']->id, 'send-reservation-created-captain-notice');
        $encode = json_decode($template->content);
        $data['reservationNumber'] = $encode->reservationNumber;
        $data['forRace'] = $encode->forRace;
        $data['respect'] = $encode->respect;
        $data['likeCaptain'] = $encode->likeCaptain;
        $data['notice'] = $encode->notice;
        $data['goodLuck'] = $encode->goodLuck;
        $data['downloadBill'] = $encode->downloadBill;
        $data['sbr'] = $encode->sbr;
        $data['spots'] = $encode->spots;

        $pdf = $this->exportService->exportToPdf($reservation, 'invoice', true);

        $this->sendEmail($data['captain']->email, $data, $title, 'view', 'mail.send-reservation-created-captain-notice', 'Business Run', $pdf);
        
        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', 'mail.send-reservation-created-captain-notice', 'Business Run', $pdf);

        $template1 = $this->getRightTemplate($data['organizer'], 'send-reservation-created-organizer-notice');
        $encode1 = json_decode($template1->content);
        $data['reservationNumber'] = $encode1->reservationNumber;
        $data['forRace'] = $encode1->forRace;
        $data['company'] = $encode1->company;
        $data['newReservation'] = $encode1->newReservation;

        $this->sendEmail($data['organizer']->email, $data, $title, 'view', 'mail.send-reservation-created-organizer-notice', 'Business Run', $pdf);

        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', 'mail.send-reservation-created-organizer-notice', 'Business Run', $pdf);
    }
    
    public function sendReservationUpdatedCaptainNotice(Reservation $reservation)
    {
        $data['captain'] = Captain::find($reservation->captain_id);
        $data['organizer'] = Organizer::find($data['captain']->organizer_id);
        $data['race'] = Race::find($reservation->race_id);
        $data['reservation'] = $reservation;
        $title = __('Business Run: You have edited a reservation'). ' #' . $reservation->id . ' '. __('for the race') .' ' . $data['race']->name;        
        $template = $this->getRightTemplate($data['organizer']->id, 'send-reservation-created-captain-notice');
        $encode = json_decode($template->content);
        $data['reservationNumber'] = $encode->reservationNumber;
        $data['forRace'] = $encode->forRace;
        $data['respect'] = $encode->respect;
        $data['likeCaptain'] = $encode->likeCaptain;
        $data['notice'] = $encode->notice;
        $data['goodLuck'] = $encode->goodLuck;
        $data['downloadBill'] = $encode->downloadBill;
        $data['sbr'] = $encode->sbr;
        $data['spots'] = $encode->spots;

        $pdf = $this->exportService->exportToPdf($reservation, 'invoice', true);

        $this->sendEmail($data['captain']->email, $data, $title, 'view', 'mail.send-reservation-updated-captain-notice', 'Business Run', $pdf);
        
        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', 'mail.send-reservation-updated-captain-notice', 'Business Run', $pdf);

        $template1 = $this->getRightTemplate($data['organizer'], 'send-reservation-created-organizer-notice');
        $encode1 = json_decode($template1->content);
        $data['reservationNumber'] = $encode1->reservationNumber;
        $data['forRace'] = $encode1->forRace;
        $data['company'] = $encode1->company;
        $data['newReservation'] = $encode1->newReservation;

        $this->sendEmail($data['organizer']->email, $data, $title, 'view', 'mail.send-reservation-updated-organizer-notice', 'Business Run', $pdf);

        //$this->sendEmail('denis@blue-bear.hr', $data, $title, 'view', 'mail.send-reservation-updated-organizer-notice', 'Business Run', $pdf);
    }

    public function sendResetPasswordMail($data) 
    {
        $template = $this->getRightTemplate($data['organizerId'], 'reset-password-notice');
        $encode = json_decode($template->content);
        $data['passwordChanged'] = $encode->passwordChange;
        $data['newPassword'] = $encode->newPassword;

        $this->sendEmail($data['email'], $data, 'Password Reset', 'view', 'mail.reset-password-notice');

        //$this->sendEmail('denis@blue-bear.hr', $data, 'Password Reset', 'view', 'mail.reset-password-notice');
    }

    public function getRightTemplate($organizerID, $component) 
    {
        $template = Translation::where([['organizer_id', $organizerID], ['component', $component]]) 
            ->first();

        return $template;
    }

    public function sendFreeSpotsEmail($data, $freeSpotsNumber, $date, $reservationID, $race) 
    {       
        $template = $this->getRightTemplate($data['organizer_id'], 'free-spots');
        $encode = json_decode($template->content);
        $data['title'] = $encode->title;
        $data['respect'] = $encode->respect;
        $data['race'] = $race;
        $data['freeSpots'] = $encode->freeSpots;
        $data['freeSpots1'] = $encode->freeSpots1;
        $data['freeSpots2'] = $encode->freeSpots2;
        $data['freeSpotsNumber'] = $freeSpotsNumber;
        $data['date'] = $date;
        $data['reservationID'] = $reservationID;

        $this->sendEmail($data['email'], $data, $data['title'], 'view', 'mail.free-spots');

        //$this->sendEmail('denis@blue-bear.hr', $data, $data['title'], 'view', 'mail.free-spots');
    }
    public function sendIntervalEndingEmail($captain, $diffInDays, $race) 
    {
        $template = $this->getRightTemplate($captain['organizer_id'], 'interval-ending');
        $encode = json_decode($template->content);
        $data['interval'] = $encode->interval;
        $data['greetings'] = $encode->greetings;
        $data['interval1'] = $encode->interval1;
        $data['interval2'] = $encode->interval2;
        $data['interval3'] = $encode->interval3;
        $data['interval4'] = $encode->interval4;
        $data['title'] = $encode->title;
        $data['diffInDays'] = $diffInDays;
        $data['race'] = $race;

        $this->sendEmail($captain['email'], $data, $data['title'], 'view', 'mail.interval-ending');

        //$this->sendEmail('denis@blue-bear.hr', $data, $data['title'], 'view', 'mail.interval-ending');
    }

    public function deletedReservation($reservationID, $teamName, $reservedPlaces, $organizer) 
    {     
        $reservation = Reservation::find($reservationID);
        
        $data['title'] = __('Reservation deleted');
        $data['reservationID'] = $reservationID;
        $data['teamName'] = $teamName;
        $data['reservedPlaces'] = $reservedPlaces;
        $data['raceName'] = $reservation->race->name;

        $this->sendEmail($organizer->email, $data, $data['title'], 'view', 'mail.reservation-deleted', 'No Reply'); 
        
        //$this->sendEmail('denis@blue-bear.hr', $data, $data['title'], 'view', 'mail.reservation-deleted', 'No Reply'); 
    }
}