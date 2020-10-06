<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\TrajetRepository")
 */
class Trajet
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $cp1;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $adresse1;

    /**
     * @ORM\Column(type="string", length=5)
     */
    private $cp2;

    /**
     * @ORM\Column(type="string", length=100)
     */
    private $adresse2;

    /**
     * @ORM\Column(type="datetime")
     */
    private $hour;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nbPassager;

    /**
     * @ORM\Column(type="integer")
     */
    private $user_id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $depart;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $arrive;

    /**
     * @ORM\Column(type="float")
     */
    private $prix;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCp1(): ?string
    {
        return $this->cp1;
    }

    public function setCp1(string $cp1): self
    {
        $this->cp1 = $cp1;

        return $this;
    }

    public function getAdresse1(): ?string
    {
        return $this->adresse1;
    }

    public function setAdresse1(string $adresse1): self
    {
        $this->adresse1 = $adresse1;

        return $this;
    }

    public function getCp2(): ?string
    {
        return $this->cp2;
    }

    public function setCp2(string $cp2): self
    {
        $this->cp2 = $cp2;

        return $this;
    }

    public function getAdresse2(): ?string
    {
        return $this->adresse2;
    }

    public function setAdresse2(string $adresse2): self
    {
        $this->adresse2 = $adresse2;

        return $this;
    }

    public function getHour(): ?\DateTimeInterface
    {
        return $this->hour;
    }

    public function setHour(\DateTimeInterface $hour): self
    {
        $this->hour = $hour;

        return $this;
    }

    public function getNbPassager(): ?string
    {
        return $this->nbPassager;
    }

    public function setNbPassager(string $nbPassager): self
    {
        $this->nbPassager = $nbPassager;

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->user_id;
    }

    public function setUserId(int $user_id): self
    {
        $this->user_id = $user_id;

        return $this;
    }

    public function getDepart(): ?string
    {
        return $this->depart;
    }

    public function setDepart(string $depart): self
    {
        $this->depart = $depart;

        return $this;
    }

    public function getArrive(): ?string
    {
        return $this->arrive;
    }

    public function setArrive(string $arrive): self
    {
        $this->arrive = $arrive;

        return $this;
    }

    public function getPrix(): ?float
    {
        return $this->prix;
    }

    public function setPrix(float $prix): self
    {
        $this->prix = $prix;

        return $this;
    }

}
