<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

use Serializable;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @UniqueEntity(
 * fields={"email"},
 * message="Cette email est déjà utilisé !"
 * )
 * @UniqueEntity(
 * fields={"username"},
 * message="Ce nom utilisateur est déjà utilisé !"
 * )
 */
class User implements UserInterface, \Serializable
{

    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ));
    }
 
    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->username,
            $this->email,
            $this->password
        ) = unserialize($serialized, array('allowed_classes' => false));
    }


    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Email()
     */
    private $email;

    /**
     * @Assert\Email()
     */
    private $reset_password_email;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    private $password;

    /**
     * @Assert\EqualTo(propertyPath="password", message="Vous n'avez pas tapé le même mot de passe")
     */
    public $confirm_password;

    /**
     * @Assert\Length(min="8", minMessage="Votre mot de passe doit faire minimum 8 caractères")
     */
    public $new_password;

    /**
     * @Assert\EqualTo(propertyPath="new_password", message="Vous n'avez pas tapé le même mot de passe")
     */
    public $confirm_new_password;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $avatar;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $banner;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $confirmEmailToken;

    /**
     * @ORM\Column(type="string", unique=true, nullable=true)
     */
    private $resetPasswordToken;

    /**
     * @ORM\Column(type="integer", length=1)
     */
    private $isValidated;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getResetPasswordEmail(): ?string
    {
        return $this->reset_password_email;
    }

    public function setResetPasswordEmail(string $reset_password_email): self
    {
        $this->reset_password_email = $reset_password_email;

        return $this;
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

    public function getNewPassword(): ?string
    {
        return $this->new_password;
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

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getAvatar()
    {
        return $this->avatar;
    }

    public function setAvatar($avatar): self
    {
        $this->avatar = $avatar;

        return $this;
    }

    public function getBanner()
    {
        return $this->banner;
    }

    public function setBanner($banner): self
    {
        $this->banner = $banner;

        return $this;
    }

    public function getConfirmEmailToken(): ?string
    {
        return $this->confirmEmailToken;
    }

    public function setConfirmEmailToken(string $confirmEmailToken): self
    {
        $this->confirmEmailToken = $confirmEmailToken;

        return $this;
    }

    public function getResetPasswordToken()
    {
        return $this->resetPasswordToken;
    }

    public function setResetPasswordToken($resetPasswordToken)
    {
        $this->resetPasswordToken = $resetPasswordToken;

        return $this;
    }

    public function getIsValidated(): ?int
    {
        return $this->isValidated;
    }

    public function setIsValidated(int $isValidated): self
    {
        $this->isValidated = $isValidated;

        return $this;
    }

    public function eraseCredentials() {}
    public function getSalt() {}

    public function getRoles() {
        return ['ROLE_USER'];
    }
}
