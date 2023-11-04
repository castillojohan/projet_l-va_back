<?php

namespace App\Entity;

use DateTime;
use App\Repository\CaseFolderRepository;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=CaseFolderRepository::class)
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity("reference")
 */
class CaseFolder
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users_get_collection", "cases_get_collections", "reported_get_item", "cases_get_links", "platforms_get_collection"})
     */
    private $id;

    /**
     * @ORM\Column(type="text")
     * @Groups({"users_get_collection", "case_get_item", "cases_get_collections", "backoffice_get_collection"})
     * @Assert\NotBlank
     */
    private $content;

    /**
     * @ORM\Column(type="json")
     * @Groups({"case_get_item", "cases_get_collections", "backoffice_get_collection"})
     */
    private $status = ["AWAITING"];

    /**
     * @ORM\Column(type="datetime")
     * @Groups({"users_get_collection", "cases_get_collections", "cases_get_links", "backoffice_get_collection"})
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"cases_get_collections", "users_get_collection"})
     */
    private $updatedAt;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="caseFolders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"cases_get_collections"})
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity=Reported::class, inversedBy="caseFolders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"users_get_collection", "user_get_item", "cases_get_collections", "backoffice_get_collection"})
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    private $reported;

    /**
     * @ORM\ManyToOne(targetEntity=Platform::class, inversedBy="caseFolders")
     * @ORM\JoinColumn(nullable=false)
     * @Groups({"users_get_collection", "user_get_item", "cases_get_collections", "backoffice_get_collection"})
     * @Assert\NotBlank
     * @Assert\NotNull
     */
    private $platform;

    /**
     * @ORM\Column(type="string", length=255)
     * @Groups({"cases_get_collections", "case_get_item", "users_get_collection"})
     */
    private $reference;

    /**
     * @ORM\OneToMany(targetEntity=Screenshots::class, mappedBy="CaseFolder", orphanRemoval=true)
     * @Groups("case_get_screenshots")
     */
    private $screenshots;

    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->screenshots = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): self
    {
        $this->content = $content;

        return $this;
    }

    public function getStatus(): ?array
    {
        return $this->status;
    }

    public function setStatus(array $status): self
    {
        $this->status = $status;

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getReported(): ?Reported
    {
        return $this->reported;
    }

    public function setReported(?Reported $reported): self
    {
        $this->reported = $reported;

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

    public function getReference(): ?string
    {
        return $this->reference;
    }

    /**
     * @ORM\PrePersist
     *
     * set reference of a case
     */
    public function setReference($reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    /**
     * To generate endpoint API
     * @Groups({"cases_get_collection", "reported_detail", "cases_get_links", "platforms_get_collection"})
     */
    public function getApiEndpointCase()
    {
        return "/api/back/cases/{$this->id}/view";
    }

    /**
     * @return Collection<int, Screenshots>
     */
    public function getScreenshots(): Collection
    {
        return $this->screenshots;
    }

    public function addScreenshot(Screenshots $screenshot): self
    {
        if (!$this->screenshots->contains($screenshot)) {
            $this->screenshots[] = $screenshot;
            $screenshot->setCaseFolder($this);
        }

        return $this;
    }

    public function removeScreenshot(Screenshots $screenshot): self
    {
        if ($this->screenshots->removeElement($screenshot)) {
            // set the owning side to null (unless already changed)
            if ($screenshot->getCaseFolder() === $this) {
                $screenshot->setCaseFolder(null);
            }
        }

        return $this;
    }
}
