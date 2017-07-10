<?php

namespace AppBundle\Entity\Wit;

use DateTime;
use DateTimeZone;
use Doctrine\ORM\Mapping as ORM;

/**
 * Class WitResponse
 * Store responses from wit.ai
 * @package AppBundle\Entity\Wit
 *
 * @ORM\Table(name="wit_response")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\Wit\WitResponseRepository")
 */
class WitResponse
{

    /**
     * @var string $msgid
     * @ORM\Id
     * @ORM\Column(name="id", type="string")
     */
    private $msgid;

    /**
     * Original text send by user
     * @var string $text
     * @ORM\Column(name="message", type="string", length=255)
     */
    private $text;

    /**
     * List of WIT entities
     * @see https://wit.ai/docs/http/20170307#get--message-link
     * @var array $entities
     * @ORM\Column(type="json_array")
     */
    private $entities;

    /**
     * @var DateTime $createdAt
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $createdAt;

    /**
     * @var array $intents
     */
    private $intents;

    /**
     *
     * /**
     * WitResponse constructor.
     * @param array $response
     */
    public function __construct(array $response)
    {
        $this->createdAt = new DateTime('now', new DateTimeZone('Europe/Paris'));
        if ($response) {
            foreach ($response as $key => $value) {
                $method = 'set' . ucfirst(str_replace('_', '', $key));
                if (method_exists($this, $method)) {
                    $this->$method($value);
                }
            }
        }

        if (count($this->getEntities()) > 0 && isset($this->entities['intent'])) {
            $intents = $this->getEntities()['intent'];
            foreach ($intents as $intent) {
                $this->intents[] = $intent['value'];
            }
            unset($this->entities['intent']);
        }
    }

    /**
     * @return string
     */
    public function getMsgid()
    {
        return $this->msgid;
    }

    /**
     * @param mixed $msgid
     */
    public function setMsgid($msgid)
    {
        $this->msgid = $msgid;
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param mixed $text
     */
    public function setText($text)
    {
        $this->text = $text;
    }

    /**
     * @return mixed
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * @param mixed $entities
     */
    public function setEntities($entities)
    {
        $this->entities = $entities;
    }

    /**
     * @return DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * @param DateTime $createdAt
     */
    public function setCreatedAt(DateTime $createdAt)
    {
        $this->createdAt = $createdAt;
    }

    /**
     * @return array
     */
    public function getIntents(): array
    {
        return $this->intents;
    }

    /**
     * @param array $intents
     */
    public function setIntents(array $intents)
    {
        $this->intents = $intents;
    }

    /**
     * @param $key
     * @return bool
     */
    public function hasIntent($key)
    {
        if (isset($this->intents)) {
            return in_array($key, $this->intents);
        } else {
            return false;
        }
    }

    public function hasEntity($key)
    {
        return isset($this->entities[$key]);
    }

    public function getEntity($key)
    {
        return isset($this->entities[$key]) ? $this->entities[$key] : [];
    }
}