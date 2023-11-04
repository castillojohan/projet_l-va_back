<?php

namespace App\Entity;

use App\Repository\ReportedRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=ReportedRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @Assert\DisableAutoMapping
 */
class Reported
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("reporteds_get_collection")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"users_get_collection","users_get_item", "case_get_item", "reporteds_get_collection", "cases_get_collections", "backoffice_get_collection"})
     * @Assert\NotBlank
     */
    private $reportedPseudo;

    /**
     * @ORM\Column(type="integer")
     * @Groups({"reporteds_get_collection", "cases_get_collections"})
     */
    private $reportedNumber;

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"reported_get_item", "reporteds_get_collection"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"reporteds_get_collection", "reported_get_item"})
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=CaseFolder::class, mappedBy="reported", orphanRemoval=true)
     */
    private $caseFolders;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class, inversedBy="reporteds")
     * @ORM\JoinColumn(nullable=false)
     */
    private $platform;

    public function __construct()
    {
        $this->caseFolders = new ArrayCollection();
        $this->createdAt = new DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReportedPseudo(): ?string
    {
        return $this->reportedPseudo;
    }

    public function setReportedPseudo(string $reportedPseudo): self
    {
        $this->reportedPseudo = $reportedPseudo;

        return $this;
    }

    public function getReportedNumber(): ?int
    {
        return $this->reportedNumber;
    }

    public function setReportedNumber(int $reportedNumber): self
    {
        $this->reportedNumber = $reportedNumber;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeInterface
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeInterface $updatedAt): self
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * @return Collection<int, CaseFolder>
     */
    public function getCaseFolders(): Collection
    {
        return $this->caseFolders;
    }

    public function addCaseFolder(CaseFolder $caseFolder): self
    {
        if (!$this->caseFolders->contains($caseFolder)) {
            $this->caseFolders[] = $caseFolder;
            $caseFolder->setReported($this);
        }

        return $this;
    }

    public function removeCaseFolder(CaseFolder $caseFolder): self
    {
        if ($this->caseFolders->removeElement($caseFolder)) {
            // set the owning side to null (unless already changed)
            if ($caseFolder->getReported() === $this) {
                $caseFolder->setReported(null);
            }
        }

        return $this;
    }

    public function getPlatform(): ?Platform
    {
        return $this->platform;
    }

    public function setPlatform(?Platform $platform): self
    {
        $this->platform = $platform;

        return $this;
    }

    /**
     * Method who increment reportedNumber of a Reported, and apply datetime on updated_at
     */
    public function incrementReportedNbr()
    {
        $this->reportedNumber += 1;
        $this->updatedAt = new \DateTime();
    }
}
