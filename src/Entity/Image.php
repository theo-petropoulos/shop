<?php

namespace App\Entity;

use App\Exceptions\InvalidSizeException;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Config\Definition\Exception\InvalidTypeException;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\File\UploadedFile;

#[ORM\Entity(repositoryClass: ImageRepository::class)]
class Image
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    protected int $id;

    #[ORM\ManyToOne(targetEntity: Product::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'cascade')]
    private Product $product;

    #[ORM\ManyToOne(targetEntity: Author::class, inversedBy: 'images')]
    #[ORM\JoinColumn(nullable: true, onDelete: 'cascade')]
    private Author $author;

    #[ORM\Column(type: 'string', length: 255)]
    private string $name;

    #[ORM\Column(type: 'string', length: 255)]
    private string $path;

    private ?UploadedFile $file;

    public function __construct(?UploadedFile $file)
    {
        $this->file = $file;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Product
    {
        return $this->product;
    }

    public function setProduct(?Product $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getAuthor(): ?Author
    {
        return $this->author;
    }

    public function setAuthor(?Author $author): self
    {
        $this->author = $author;

        return $this;
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

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(string $path): self
    {
        $this->path = $path;

        return $this;
    }

    /**
     * @throws InvalidSizeException|InvalidTypeException|UploadException
     */
    public function upload(string $path): void
    {
        // Check if the file isn't too voluminous and if it was correctly uploaded to the temp folder
        if ($this->file->getSize() < 10000000 && $this->file->getError() === UPLOAD_ERR_OK) {
            $extension = $this->file->guessExtension();

            // Check if the file is from a correct MIME type
            if (in_array($extension, ['jpeg', 'jpg', 'gif', 'bmp', 'png'])) {
                $fileName = uniqid() . '.' . $this->file->guessExtension();

                // Check if the file has been correctly moved to the products' images directory
                if ($this->file->move($path, $fileName)) {
                    $this->name = $fileName;
                    $this->path = $path . '/' . $fileName;
                }
                else
                    throw new UploadException('Le fichier n\'a pas pu être enregistré dans le répertoire de destination.');
            }
            else
                throw new InvalidTypeException('Le fichier ne possède pas une extension d\'image valide.');
        }
        else
            throw new InvalidSizeException('Le fichier est trop volumineux.');
    }
}
