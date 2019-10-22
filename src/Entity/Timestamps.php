<?php

namespace App\Entity;

/**
 *
 */
trait Timestamps
{

  /**
   * @ORM\Column(type="datetime")
   */
  private $createdAt;
  /**
   * @ORM\Column(type="datetime")
   */
  private $updatedAt;

  /**
   * @ORM\PrePersist()
   */
  public function created()
  {
    $this->createdAt = new \DateTime();
    $this->updatedAt = new \DateTime();
  }
  /**
   * @ORM\PreUpdate()
   */
  public function updated()
  {
    $this->updatedAt = new \DateTime();
  }

  /**
   * Get the value of createdAt
   */
  public function getCreatedAt()
  {
    return $this->createdAt;
  }

  /**
   * Get the value of updatedAt
   */
  public function getUpdatedAt()
  {
    return $this->updatedAt;
  }
}
