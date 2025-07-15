<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use SimpleXMLElement;
use Spatie\ArrayToXml\ArrayToXml;

class SoapController extends Controller
{
    public function handleExternalRequest(Request $request)
    {
        $soapRequestContent = $request->getContent();

        $xml = new SimpleXMLElement($soapRequestContent);

        $xml->registerXPathNamespace('soap', 'http://schemas.xmlsoap.org/soap/envelope/');

        $taskCount = count($xml->xpath('//task'));

        $soapResponse = <<<XML
    <soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/">
        <soap:Body>
            <ProcessTasksResponse xmlns="http://www.example.com/taskservice">
                <Status>Success</Status>
                <TasksReceived>{$taskCount}</TasksReceived>
            </ProcessTasksResponse>
        </soap:Body>
    </soap:Envelope>
    XML;

        return response($soapResponse, 200, ['Content-Type' => 'application/xml']);
    }

    public function sendTasksViaSoap(Request $request)
    {
        $tasks = Auth::user()->tasks()->get()->toArray();
        if (empty($tasks)) {
            return response()->json(['message' => 'No tasks to export.'], 400);
        }

        $cleanedTasks = array_map(function ($task) {
            $task['title'] = htmlspecialchars($task['title'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['description'] = htmlspecialchars($task['description'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['status'] = htmlspecialchars($task['status'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['due_date'] = htmlspecialchars($task['due_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['notification_sent_at'] = htmlspecialchars($task['notification_sent_at'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['reminder_minutes_before'] = htmlspecialchars($task['reminder_minutes_before'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['deleted_at'] = htmlspecialchars($task['deleted_at'] ?? '', ENT_QUOTES, 'UTF-8');
            $task['created_at'] = htmlspecialchars($task['created_at'], ENT_QUOTES, 'UTF-8');
            $task['updated_at'] = htmlspecialchars($task['updated_at'], ENT_QUOTES, 'UTF-8');

            $nullableFields = ['notification_sent_at', 'reminder_minutes_before', 'attachment_path', 'deleted_at'];
            foreach ($nullableFields as $field) {
                if ($task[$field] === '') {
                    $task[$field] = null;
                }
            }
            return $task;
        }, $tasks);

        $xml = ArrayToXml::convert(['task' => $cleanedTasks], [
            'rootElementName' => 'tasks',
            '_attributes' => [],
        ]);


        // La URL de nuestro servidor falso
        $endpoint = url('/api/mock-soap-server');

        try {

            $response = Http::withToken($request->bearerToken())
            ->withHeaders([
                'Content-Type' => 'text/xml; charset=utf-8',
            ])->withBody($xml, 'application/xml')->post($endpoint);

            if ($response->failed()) {
                throw new \Exception('SOAP server responded with an error.');
            }

            // Procesamos la respuesta del servidor SOAP
            $responseXml = new SimpleXMLElement($response->body());
            $responseXml->registerXPathNamespace('res', 'http://www.example.com/taskservice');
            $status = (string) $responseXml->xpath('//res:Status')[0];
            $tasksReceived = (string) $responseXml->xpath('//res:TasksReceived')[0];

            return response()->json([
                'message' => 'Tasks successfully sent to SOAP service.',
                'server_status' => $status,
                'tasks_processed_by_server' => $tasksReceived
            ]);
        } catch (\Exception $e) {
            Log::error('SOAP Client Error: ' . $e->getMessage());
            return response()->json(['message' => 'Failed to send data to SOAP service.'], 500);
        }
    }
}
