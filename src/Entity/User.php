<?php

namespace App\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @ORM\Table(name="`user`")
 * @ORM\HasLifecycleCallbacks()
 * @UniqueEntity(fields="pseudo")
 * @UniqueEntity(fields="email")
 */
class User implements  UserInterface, PasswordAuthenticatedUserInterface
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups({"users_get_collection", "user_get_item", "messages_get_collection", "messages_get_recipient"})
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=70, unique= true)
     * @Groups({"users_get_collection", "user_get_item", "cases_get_collections", "proposals_get_collection", "messages_get_collection"})
     * @Assert\NotBlank
     * @Assert\Email(
     * message = "L'email '{{ value }}' n'est pas un mail valide"
     * )
     */
    private $email;


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Assert\Regex(
     *     pattern="/^(?=.*[A-Z])(?=.*\d)(?=.*[\W_]).{8,}$/",
     *     message="Le mot de passe ne contient pas les exigences minimum, 8 lettres, au moins une majuscule, au moins un charactere spécial. ")
     */
    private $password;


    /**
     * @ORM\Column(type="json")
     * @Assert\NotBlank
     * @Groups({"user_get_role", "users_get_collection"})
     */
    private $roles=["ROLE_USER"];


    /**
     * @ORM\OneToMany(targetEntity=Proposal::class, mappedBy="user")
     */
    private $proposals;


    /**
     * @ORM\Column(type="datetime")
     * @Groups("user_get_item")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private $updatedAt;

    /**
     * @ORM\OneToMany(targetEntity=CaseFolder::class, mappedBy="user")
     * @Groups({"user_get_item", "users_get_collection"})
     */
    private $caseFolders;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="sender", orphanRemoval=true)
     */
    private $sent;

    /**
     * @ORM\OneToMany(targetEntity=Message::class, mappedBy="recipient", orphanRemoval=true)
     * 
     */
    private $received;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     * @Groups({"user_get_item", "users_get_collection", "cases_get_collections", "proposals_get_collection", "messages_get_recipient"})
     */
    private $pseudo;


    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"user_get_item"})
     */
    private $forgotPasswordToken;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $forgotPasswordTokenCreatedAt;

    /**
     * @ORM\Column(type="datetime_immutable", nullable=true)
     */
    private $forgotPasswordTokenExpireAfter;



    public function __construct()
    {
        $this->createdAt = new DateTime();
        $this->proposals = new ArrayCollection();
        $this->caseFolders = new ArrayCollection();
        $this->sent = new ArrayCollection();
        $this->received = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
    }
  

    public function getPassword(): ?string
    {
        return $this->password;
    }


    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }


    public function getRole(): ?array
    {
        return $this->roles;
    }


    public function setRole(array $role): self
    {
        $this->roles= $role;

        return $this;
    }


    /**
     * @return Collection<int, Proposal>
     */
    public function getProposals(): Collection
    {
        return $this->proposals;
    }


    public function addProposal(Proposal $proposal): self
    {
        if (!$this->proposals->contains($proposal)) {
            $this->proposals[] = $proposal;
            $proposal->setUser($this);
        }

        return $this;
    }


    public function removeProposal(Proposal $proposal): self
    {
        if ($this->proposals->removeElement($proposal)) {
            // set the owning side to null (unless already changed)
            if ($proposal->getUser() === $this) {
                $proposal->setUser(null);
            }
        }

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
            $caseFolder->setUser($this);
        }

        return $this;
    }


    public function removeCaseFolder(CaseFolder $caseFolder): self
    {
        if ($this->caseFolders->removeElement($caseFolder)) {
            // set the owning side to null (unless already changed)
            if ($caseFolder->getUser() === $this) {
                $caseFolder->setUser(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Message>
     */
    public function getSent(): Collection
    {
        return $this->sent;
    }


    public function addSent(Message $sent): self
    {
        if (!$this->sent->contains($sent)) {
            $this->sent[] = $sent;
            $sent->setSender($this);
        }

        return $this;
    }


    public function removeSent(Message $sent): self
    {
        if ($this->sent->removeElement($sent)) {
            // set the owning side to null (unless already changed)
            if ($sent->getSender() === $this) {
                $sent->setSender(null);
            }
        }

        return $this;
    }


    /**
     * @return Collection<int, Message>
     */
    public function getReceived(): Collection
    {
        return $this->received;
    }


    public function addReceived(Message $received): self
    {
        if (!$this->received->contains($received)) {
            $this->received[] = $received;
            $received->setRecipient($this);
        }

        return $this;
    }


    public function removeReceived(Message $received): self
    {
        if ($this->received->removeElement($received)) {
            // set the owning side to null (unless already changed)
            if ($received->getRecipient() === $this) {
                $received->setRecipient(null);
            }
        }

        return $this;
    }


    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): self
    {
        $this->pseudo = $pseudo;

        return $this;
    }


    /**
     * Get the value of email
     */
    public function getEmail()
    {
        return $this->email;
    }

    
    /**
     * Set the value of email
     *
     * @return  self
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }
    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }
    
    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        //guarantee every user at least has ROLE_USER
        // $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */
    public function getUsername(): string
    {
        return (string) $this->email;
    }

    public function getForgotPasswordToken(): ?string
    {
        return $this->forgotPasswordToken;
    }

    public function setForgotPasswordToken(?string $forgotPasswordToken): self
    {
        $this->forgotPasswordToken = $forgotPasswordToken;

        return $this;
    }

    public function getForgotPasswordTokenCreatedAt(): ?\DateTimeImmutable
    {
        return $this->forgotPasswordTokenCreatedAt;
    }

    public function setForgotPasswordTokenCreatedAt(?\DateTimeImmutable $forgotPasswordTokenCreatedAt): self
    {
        $this->forgotPasswordTokenCreatedAt = $forgotPasswordTokenCreatedAt;

        return $this;
    }

    public function getForgotPasswordTokenExpireAfter(): ?\DateTimeImmutable
    {
        return $this->forgotPasswordTokenExpireAfter;
    }

    public function setForgotPasswordTokenExpireAfter(?\DateTimeImmutable $forgotPasswordTokenExpireAfter): self
    {
        $this->forgotPasswordTokenExpireAfter = $forgotPasswordTokenExpireAfter;

        return $this;
    }
}
