<?php

namespace KimiAI;

class Attachment
{
    private string $path;
    private string $mimeType;
    private ?string $filename;
    private ?string $description;

    public function __construct(
        string $path,
        string $mimeType = null,
        string $filename = null,
        string $description = null
    ) {
        $this->path = $path;
        $this->mimeType = $mimeType ?? mime_content_type($path);
        $this->filename = $filename ?? basename($path);
        $this->description = $description;
    }

    public static function fromPath(string $path, string $description = null): self
    {
        return new self($path, null, null, $description);
    }

    public function getContent(): string
    {
        return base64_encode(file_get_contents($this->path));
    }

    public function getMimeType(): string
    {
        return $this->mimeType;
    }

    public function getFilename(): string
    {
        return $this->filename;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function toArray(): array
    {
        return [
            'type' => 'file',
            'mime_type' => $this->mimeType,
            'filename' => $this->filename,
            'content' => $this->getContent(),
            'description' => $this->description,
        ];
    }
}
