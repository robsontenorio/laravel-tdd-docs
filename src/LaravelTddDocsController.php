<?php 

namespace Tenorio\Laravel\Tdd\Docs;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Filesystem\FileNotFoundException;

class LaravelTddDocsController extends Controller
{
    public function index()
    {
        $warning = '';
        $docs = new Collection();

        try {
            $report = Storage::get('testing-docs/report.xml');
            $files = File::allFiles(base_path('tests/Feature'));

            foreach ($files as $file) {                                
                if (!str_contains($file->getBaseName(), 'Test'))
                {
                    continue;
                }

                if ($file->getMTime() > Storage::lastModified('testing-docs/report.xml')) {
                    $docs = [];
                    throw new \Exception('One of your test files was <strong>modified</strong> recently. Run full <strong>phpunit</strong> suite again.');                    
                }

                $docs->add((new DocGenerator($file))->build());
            }

            if (count($docs) === 0)
            {
                throw new \Exception('No test files detected on <strong>tests/Feature</strong>. You should write some tests :)');
            }
            
            $xml = simplexml_load_string($report);
            $errors = new Collection();

            foreach ($xml->test as $element) {
                if (isset($element['exceptionMessage'])) {
                    $class = (string) $element['className'];
                    $exception = (string) $element['exceptionMessage'];
                    $line = (int) $element['exceptionLine'];
                    $method = (string) $element['methodName'];

                    $error = (object)['class' => $class, 'method' => $method, 'exception' => $exception, 'line' => $line];

                    $scenario = $docs->where('class', $class)->first()->scenarios->where('method', $method)->first();
                    $scenario->error = $error;

                    $steps = $docs->where('class', $class)->first()->scenarios->where('method', $method)->first()->steps->sortByDesc('line');

                    $c = null;
                    $e = null;

                    foreach ($steps as $step) {
                        if ($line > $step->line) {
                            $step->error = $error;
                            break;
                        }
                    }
                }
            }
        } catch (FileNotFoundException $e) {
            $warning = 'File not found <strong>' . storage_path('app') . '/testing-docs/report.xml</strong>. Have you already run full phpunit suite?';
        } catch (\Exception $e) {
            $warning = $e->getMessage();
        }

        return view('testingdocs::index', compact('docs', 'warning'));
    }
}
