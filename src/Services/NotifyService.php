<?php


namespace App\Services;


use App\Entity\User;
use App\Entity\UserOwnedInterface;
use App\Exception\SendMailFailedException;
use Mailjet\Client;
use Mailjet\Resources;

class NotifyService
{
    /**
     * @var string
     */
    private $mj_public;
    /**
     * @var string
     */
    private $mj_private;
    /**
     * @var string
     */
    private $mj_from_mail;
    /**
     * @var string
     */
    private $mj_from_name;

    public function __construct(string $mj_public, string $mj_private, string $mj_from_mail, string $mj_from_name)
    {
        $this->mj_public = $mj_public;
        $this->mj_private = $mj_private;
        $this->mj_from_mail = $mj_from_mail;
        $this->mj_from_name = $mj_from_name;
    }

    /**
     * Initialize MailJet with the environnement variables defined in .env
     *
     * @return Client
     */
    private function initializeMailJet(): Client
    {
        return new Client($this->mj_public, $this->mj_private, true, ['version' => 'v3.1']);
    }

    /**
     * Initialize the body of the Email
     *
     * @param int $templateId The id of the MailJet template
     * @param string $subject The subject of the Email
     * @param array $vars The vars the Email needs to be sent properly
     * @param UserOwnedInterface|null $userOwned
     * @param User|null $user
     * @return \array[][]
     */
    private function initializeBody(int $templateId, string $subject, array $vars, ?UserOwnedInterface $userOwned, ?User $user): array
    {
        if ($userOwned !== null) {
            $user = $userOwned->getUser();
        }
        return [
            'Messages' => [
                [
                    'From' => [
                        'Email' => $this->mj_from_mail,
                        'Name' => $this->mj_from_name,
                    ],
                    'To' => [
                        [
                            'Email' => $user->getEmail(),
                            'Name' => $user->getFirstname(),
                        ]
                    ],
                    'TemplateID' => $templateId,
                    'TemplateLanguage' => true,
                    'Subject' => $subject,
                    'Variables' => $this->initializeVars($vars),
                ],
            ],
        ];
    }

    /**
     * Initialize the variables of the Email
     *
     * @param array $vars
     * @return mixed
     */
    private function initializeVars(array $vars)
    {
        $numberVars = count($vars);
        $n = 0;
        $string = '{';
        foreach ($vars as $key => $var) {
            $n++;
            $string = $string . '"' . $key . '"' . ': ' . '"' . $var . '"';
            if ($n === $numberVars) {
                $string = $string . '}';
            } else {
                $string = $string . ',';
            }
        }
        return json_decode($string);
    }

    /**
     * Sends an email with MailJet, you have to define either a class implementing UserOwnedInterface either a User
     * if the UserOwnedInterface and the User are defined, the priority is given to the UserOwnedInterface
     *
     * @param int $templateId The id of the MailJet template
     * @param string $subject The subject of the Email
     * @param array $vars The vars the Email needs to be sent properly
     * @param UserOwnedInterface|null $userOwned
     * @param User|null $user
     * @return bool
     * @throws SendMailFailedException
     */
    public function sendMailWithMailJet(int $templateId, string $subject, array $vars, ?UserOwnedInterface $userOwned = null, ?User $user = null): bool
    {
        if ($userOwned === null && $user === null) {
            throw new SendMailFailedException();
        }
        $mj = $this->initializeMailJet();
        $body = $this->initializeBody($templateId, $subject, $vars, $userOwned, $user);
        $response = $mj->post(Resources::$Email, ['body' => $body]);
        return $response->success();
    }
}