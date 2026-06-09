<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Module;
use App\Models\Challenge;
use App\Models\ModuleItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use App\Services\ChallengeService;
use App\Http\Requests\StoreChallengeRequest;
use App\Http\Requests\UpdateChallengeRequest;

class ChallengeController extends Controller
{
    protected $challengeService;

    public function __construct(ChallengeService $challengeService)
    {
        $this->challengeService = $challengeService;
    }

    public function indexByModule($moduleId)
    {
        $module = Module::findOrFail($moduleId);
        $challenges = ModuleItem::where('module_id', $module->id)
                                ->where('itemable_type', Challenge::class)
                                ->with('itemable')
                                ->get()
                                ->pluck('itemable');
                                
        return response()->json(['data' => $challenges]);
    }

    public function show(Request $request, $id)
    {
        $challenge = Challenge::with('testCases', 'module.course')->findOrFail($id);
        
        if (!\Illuminate\Support\Facades\Gate::allows('update', $challenge->module->course)) {
            $challenge->setRelation('testCases', $challenge->testCases->where('is_hidden', false)->values());
        }

        return response()->json(['data' => $challenge]);
    }

    public function store(StoreChallengeRequest $request, $moduleId)
    {
        $module = Module::findOrFail($moduleId);
        Gate::authorize('update', $module->course);

        $challenge = $this->challengeService->createChallenge(
            $request->validated(),
            $module,
            $request->user()->id
        );

        return response()->json(['message' => 'Challenge created successfully', 'data' => $challenge], 201);
    }

    public function update(UpdateChallengeRequest $request, $id)
    {
        $challenge = Challenge::findOrFail($id);
        Gate::authorize('update', $challenge->module->course);
        
        $challenge = $this->challengeService->updateChallenge($challenge, $request->validated());

        return response()->json(['message' => 'Updated successfully', 'data' => $challenge]);
    }

    public function destroy($id)
    {
        $challenge = Challenge::findOrFail($id);
        Gate::authorize('update', $challenge->module->course);
        
        $this->challengeService->deleteChallenge($challenge);

        return response()->json(['message' => 'Deleted successfully']);
    }

    public function languages()
    {
        // IDs típicos de Judge0 CE: 71 = Python 3, 62 = Java, 54 = C++
        return response()->json([
            'data' => [
                ['id' => 71, 'name' => 'Python (3.8.1)'],
                ['id' => 62, 'name' => 'Java (OpenJDK 13.0.1)'],
                ['id' => 54, 'name' => 'C++ (GCC 9.2.0)'],
            ]
        ]);
    }
}
