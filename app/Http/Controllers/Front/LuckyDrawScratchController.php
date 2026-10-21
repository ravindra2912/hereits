<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\Controller;
use App\Models\LuckyDrawPrize;
use App\Repositories\Eloquent\LuckyDrawRepository;
use Illuminate\Http\Request;

class LuckyDrawScratchController extends Controller
{
    public function __construct(
        protected LuckyDrawRepository $luckyDrawRepository
    ) {}

    /**
     * Render the Scratch Card page for the customer.
     */
    public function scratch($business_slug, $token)
    {
        $entry = $this->luckyDrawRepository->getEntryByToken($token);

        $draw = $entry->luckyDraw;

        if (!$draw || $draw->status !== 'active') {
            return view('front.lucky_draw.expired', [
                'message' => 'This lucky draw campaign is no longer active.'
            ]);
        }

        // If prize not yet assigned, pre-determine securely on server
        if (!$entry->won_prize_id) {
            $wonPrize = $this->luckyDrawRepository->determinePrize($draw);
            if ($wonPrize) {
                $entry->won_prize_id = $wonPrize->id;
                $entry->save();
                $entry->load('wonPrize');
            }
        }

        // All prizes for display in the cards grid in random order
        $allPrizes = LuckyDrawPrize::where('lucky_draw_id', $draw->id)->inRandomOrder()->get();

        $hasActivePrizes = $allPrizes->contains(function ($prize) {
            return (float) $prize->win_percentage > 0;
        });

        if (!$hasActivePrizes) {
            return view('front.lucky_draw.expired', [
                'message' => 'No active prize items available for this lucky draw campaign.'
            ]);
        }

        $wonPrize = $entry->wonPrize;
        $scratchedIndex = $entry->scratched_card_index;

        $prizesArray = $allPrizes->values()->map(function ($prize) {
            $prize->image_url = getImage($prize->image_path);
            return $prize;
        })->all();

        // If already scratched, swap array elements so winning card is rendered at $scratchedIndex with zero duplicates
        if ($entry->status === 'scratched' && !is_null($scratchedIndex) && isset($prizesArray[$scratchedIndex])) {
            $foundOriginalIndex = null;
            foreach ($prizesArray as $idx => $p) {
                if ($wonPrize && $p->id == $wonPrize->id) {
                    $foundOriginalIndex = $idx;
                    break;
                }
            }

            if (!is_null($foundOriginalIndex) && $foundOriginalIndex != $scratchedIndex) {
                $temp = $prizesArray[$scratchedIndex];
                $prizesArray[$scratchedIndex] = $prizesArray[$foundOriginalIndex];
                $prizesArray[$foundOriginalIndex] = $temp;
            }
        }

        if ($draw->type === 'spinner') {
            return view('front.lucky_draw.spinner', compact('entry', 'draw', 'prizesArray', 'wonPrize', 'scratchedIndex'));
        }

        return view('front.lucky_draw.scratch', compact('entry', 'draw', 'prizesArray', 'wonPrize', 'scratchedIndex'));
    }

    /**
     * AJAX endpoint called when scratch threshold is completed.
     */
    public function claim(Request $request, $business_slug, $token)
    {
        try {
            $scratchedIndex = $request->has('scratched_card_index') ? (int) $request->scratched_card_index : null;
            $entry = $this->luckyDrawRepository->claimScratch($token, $scratchedIndex);

            $prize = $entry->wonPrize;

            return response()->json([
                'success'      => true,
                'prize_name'   => $prize ? $prize->name : 'Prize Revealed!',
                'prize_image'  => getImage($prize ? $prize->image_path : ''),
                'scratched_at' => $entry->scratched_at->format('M d, Y h:i A'),
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => checkResponse($e)]);
        }
    }
}
