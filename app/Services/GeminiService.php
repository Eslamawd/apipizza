<?php
// File: app/Services/GeminiService.php

namespace App\Services;

use Gemini\Laravel\Facades\Gemini;
use Gemini\Data\Content;
use Gemini\Enums\Role;
use Illuminate\Support\Facades\Log;
// لا نحتاج إلى Spatie\PdfToText\Pdf بعد الآن
// use Spatie\PdfToText\Pdf;
use Gemini\Enums\MimeType;
use Gemini\Data\Blob;

class GeminiService
{
    /**
     * @param string $text
     * @return string
     */
    public function generateText(string $text): string
    {
        try {
            // Send text to the Gemini model
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')
                ->generateContent($text);

            // Extract text from the response
            return $response->text();
        } catch (\Exception $e) {
            // Handle errors and return an appropriate message
            Log::error('Error in generateText: ' . $e->getMessage());
            return 'An error occurred: ' . $e->getMessage();
        }
    }

    /**
     * @param string $text
     * @param string $imagePath
     * @return string
     */
    public function generateTextWithImage(string $text, string $imagePath): string
    {
        try {
            // Check if the file exists
            if (!file_exists($imagePath)) {
                return 'Image not found.';
            }

            $imageBlob = new Blob(
                mimeType: MimeType::IMAGE_JPEG, // or IMAGE_PNG depending on the image type
                data: base64_encode(file_get_contents($imagePath))
            );

            // Send text and image to the Gemini model
            $response = Gemini::generativeModel(model: 'gemini-2.0-flash')
                ->generateContent([$text, $imageBlob]);

            return $response->text();
        } catch (\Exception $e) {
            Log::error('Error in generateTextWithImage: ' . $e->getMessage());
            return 'An error occurred: ' . $e->getMessage();
        }
    }

    /**
     * @param string $pdfPath
     * @return string
     */
    public function generateFromPdf(string $pdfPath, string $userPrompt = ''): string
{
    if (!class_exists(\Imagick::class)) {
        Log::error('Imagick extension not found.');
        return 'Imagick extension is not installed or enabled.';
    }

    try {
        $imagick = new \Imagick();
        $imagick->readImage($pdfPath);

        $blobs = [];
        foreach ($imagick as $page) {
            $page->setResolution(300, 300);
            $page->setImageFormat('jpeg');
            $blobs[] = new Blob(
                mimeType: MimeType::IMAGE_JPEG,
                data: base64_encode($page->getImageBlob())
            );
        }
$prompt = "أرجع بيانات البنود من المخططات بصيغة JSON فقط، مع حساب التكلفة الإجمالية. يجب أن يحتوي الـ JSON على حقل لكل بند يتضمن اسم البند، الكمية، وتكلفة الوحدة. أضف حقلًا جديدًا باسم   'total_cost' يحتوي على مجموع تكاليف جميع البنود.  'name' ,'unit_cost','quantity'لا تضف أي نص خارجي.";
   if (!empty($userPrompt)) {
            $prompt .= "\n" . $userPrompt;
        }

        $input = array_merge([$prompt], $blobs);

        $response = Gemini::generativeModel(model: 'gemini-2.0-flash')
            ->generateContent($input);
            Log::info($response->text());
        return $response->text();
    } catch (\Exception $e) {
        Log::error('Error in GeminiService during PDF analysis: ' . $e->getMessage());
        return 'An error occurred while analyzing the PDF file: ' . $e->getMessage();
    } finally {
        if (isset($imagick)) {
            $imagick->clear();
            $imagick->destroy();
        }
    }
}

    /**
     * @param array $history
     * @param string $message
     * @return string
     */
    public function chat(array $history, string $message): string
    {
        try {
            $parsedHistory = array_map(function ($item) {
                return Content::parse(
                    part: $item['part'],
                    role: Role::from($item['role'])
                );
            }, $history);

            $chat = Gemini::chat(model: 'gemini-2.0-flash')
                ->startChat(history: $parsedHistory);

            $response = $chat->sendMessage($message);

            return $response->text();
        } catch (\Exception $e) {
            Log::error('Error in chat: ' . $e->getMessage());
            return 'An error occurred: ' . $e->getMessage();
        }
    }
}
