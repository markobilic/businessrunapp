<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class MailerService extends Mailable
{
    use Queueable, SerializesModels;

    public $theme = 'businessrun';

    public $params;
    public $subject;
    public $fromEmail;
    public $fromName;
    public $template;
    public $template_type;
    public $data;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(array $params)
    {
        $this->params = $params;
        $this->data = isset($this->params['data']) ? $this->params['data'] : '';
        $this->subject = isset($this->params['subject']) ? $this->params['subject'] : '';
        $this->from_email = env('MAIL_FROM_ADDRESS');
        $this->from_name = env('MAIL_FROM_NAME');
        $this->template = $this->params['template'];
        $this->template_type = isset($this->params['template_type']) ? $this->params['template_type'] : 'view';
        $this->pdf = $this->params['pdf'];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        if ($this->template_type == 'view' && !$this->pdf) 
        {
            return $this->view($this->template)
                ->from($this->from_email, $this->from_name)
                ->with('data', $this->data);
        }
        else if($this->template_type == 'view' && $this->pdf) 
        {
            return $this->view($this->template)
                ->from($this->from_email, $this->from_name)
                ->with('data', $this->data)
                ->attach(storage_path('app/public/' . $this->pdf), [
                    'as' => 'invoice.pdf',
                    'mime' => 'application/pdf',
                ]);
        } 
        else 
        {
            return $this->markdown($this->template)
                ->from($this->from_email, $this->from_name)
                ->with('data', $this->data)
                ->subject($this->subject);
        }
    }
}
