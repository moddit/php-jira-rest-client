<?php

namespace JiraRestApi\Issue;

class Description implements \JsonSerializable
{
    public string $type;

    public int $version;

    public array $content;

    public ?string $text;

    #[\ReturnTypeWillChange]
    public function jsonSerialize(): ?array
    {
        $vars = get_object_vars($this);

        if (!empty($vars['content'])) {
            $vars['text'] = $this->toPlainText($vars['content']);
        }

        return $vars;
    }

    /**
     * Function to convert Atlassian document format to plain text
     */
    private function toPlainText(array $nodes): string
    {
        $text = '';

        foreach ($nodes as $node) {
            $type = $node->type ?? null;

            switch ($type) {
                case 'text':
                    $text .= $node->text ?? '';
                    break;

                case 'hardBreak':
                    $text .= "\n";
                    break;

                case 'paragraph':
                    if (!empty($node->content)) {
                        $text .= $this->toPlainText($node->content);
                    }
                    $text .= "\n\n";
                    break;

                case 'bulletList':
                    if (!empty($node->content)) {
                        foreach ($node->content as $listItem) {
                            $text .= '- ' . trim($this->toPlainText($listItem->content ?? [])) . "\n";
                        }
                    }
                    $text .= "\n";
                    break;

                case 'listItem':
                    if (!empty($node->content)) {
                        $text .= $this->toPlainText($node->content);
                    }
                    break;

                case 'expand':
                    if (!empty($node->content)) {
                        $text .= $this->toPlainText($node->content);
                    }
                    break;

                case 'table':
                case 'mediaSingle':
                case 'media':
                    // skip for now
                    break;

                default:
                    if (!empty($node->content)) {
                        $text .= $this->toPlainText($node->content);
                    }
                    break;
            }
        }

        return $text;
    }
}
