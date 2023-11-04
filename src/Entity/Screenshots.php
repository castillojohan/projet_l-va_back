<?php

namespace App\Entity;

use App\Repository\ScreenshotsRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ScreenshotsRepository::class)
 */
class Screenshots
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups("case_get_screenshots")
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=CaseFolder::class, inversedBy="screenshots")
     * @ORM\JoinColumn(nullable=false)
     */
    private $CaseFolder;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getCaseFolder(): ?CaseFolder
    {
        return $this->CaseFolder;
    }

    public function setCaseFolder(?CaseFolder $CaseFolder): self
    {
        $this->CaseFolder = $CaseFolder;

        return $this;
    }

    /**
     * @Groups("case_get_screenshots")
     */
    public function getPath()
    {
        return ["originalScreen" => "/assets/uploads/".$this->name, "miniScreen" => "/assets/uploads/mini/500x500-".$this->name];
    }
}
