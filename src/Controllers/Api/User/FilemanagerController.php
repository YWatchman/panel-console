<?php

namespace YWatchman\Panel_Console\Controllers\Api\User;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Pterodactyl\Http\Controllers\Api\Client\ClientApiController as Controller;
use Pterodactyl\Contracts\Repository\Daemon\FileRepositoryInterface;
use Illuminate\Contracts\Config\Repository as ConfigRepository;
use YWatchman\Panel_Console\Requests\ListFilesRequest;
use YWatchman\Panel_Console\Requests\GetFileContentsRequest;
use YWatchman\Panel_Console\Requests\WriteFileContentRequest;
use YWatchman\Panel_Console\Requests\DeleteFileRequest;
use Pterodactyl\Models\Server;

class FilemanagerController extends Controller
{
    /**
     * @var \Pterodactyl\Contracts\Repository\Daemon\FileRepositoryInterface $repository
     * @var \Illuminate\Contracts\Config\Repository                          $config
     */
    private $repository;

    public function __construct(FileRepositoryInterface $repository, ConfigRepository $config) {
        parent::__construct();
        $this->repository = $repository;
        $this->config = $config;
    }

    public function getDirectoryListing(ListFilesRequest $request) {
        return response()->json([
            'contents' => $this->repository->setServer($request->getModel(Server::class))->getDirectory(
              $request->get('directory') ?? '/'
            ),
            'editable' => $this->config->get('pterodactyl.files.editable', [])
        ]);
    }

    public function getFileContents(GetFileContentsRequest $request) {
	try {
            $response = Response::create(
                $this->repository->setServer($request->getModel(Server::class))->getContent(
                    $request->get('file'), $this->config->get('pterodactyl.files.max_edit_size')
                )
            );
        } catch (\Exception $e) {
            return response(null, 404);
        }

        return $response;
    }

    public function writeFileContent(WriteFileContentRequest $request) {
        $this->repository->setServer($request->getModel(Server::class))->putContent(
            $request->get('file'),
            $request->getContent()
        );

        return response(null, 204);
    }

    public function deleteFile(DeleteFileRequest $request) {
        $this->repository
            ->setServer($request->getModel(Server::class))
            ->deleteFile($request->input('file')); // This function doesn't exist YET!

        return response(null, 204);
    }

}
