<?php

namespace App\Entity;

use App\Repository\PlatformRepository;
use Doctrine\ORM\Mapping as ORM;
use DateTime;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=PlatformRepository::class)
 * @ORM\HasLifecycleCallbacks()
 */
class Platform
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("platforms_get_collection")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"users_get_collection", "users_detail", "cases_detail", "cases_get_collections", "platforms_get_collection", "backoffice_get_collection"})
     */
    private $name;

    /**
     * @ORM\Column(type="datetime")
     * @Groups("platforms_get_collection")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups("platforms_get_collection")
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=CaseFolder::class, mappedBy="platform")
     * @Groups("platforms_get_collection")
     */
    private $caseFolders;

    /**
     * @ORM\OneToMany(targetEntity=Reported::class, mappedBy="platform", orphanRemoval=true)
     */
    private $reporteds;

    public function __construct()
    {
        $this->caseFolders = new ArrayCollection();
        $this->createdAt = new DateTime();
        $this->reporteds = new ArrayCollection();
    }

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
            $caseFolder->setPlatform($this);
        }

        return $this;
    }

    public function removeCaseFolder(CaseFolder $caseFolder): self
    {
        if ($this->caseFolders->removeElement($caseFolder)) {
            // set the owning side to null (unless already changed)
            if ($caseFolder->getPlatform() === $this) {
                $caseFolder->setPlatform(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Reported>
     */
    public function getReporteds(): Collection
    {
        return $this->reporteds;
    }

    public function addReported(Reported $reported): self
    {
        if (!$this->reporteds->contains($reported)) {
            $this->reporteds[] = $reported;
            $reported->setPlatform($this);
        }

        return $this;
    }

    public function removeReported(Reported $reported): self
    {
        if ($this->reporteds->removeElement($reported)) {
            // set the owning side to null (unless already changed)
            if ($reported->getPlatform() === $this) {
                $reported->setPlatform(null);
            }
        }

        return $this;
    }
}
