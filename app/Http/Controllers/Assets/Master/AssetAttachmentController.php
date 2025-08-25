<?php
namespace App\Http\Controllers\Assets\Master;

use App\Http\Controllers\Controller;
use App\Models\Assets\Master\AssetAttachment;
use Illuminate\Http\Request;

class AssetAttachmentController extends Controller
{
    public function index(int $asset)
    {
        $rows = AssetAttachment::where('AssetID',$asset)->orderByDesc('UploadedOn')->paginate(20);
        return view('assets.master.attachments.index', compact('rows','asset'));
    }

    public function create(int $asset)
    { return view('assets.master.attachments.create', compact('asset')); }

    public function store(Request $request, int $asset)
    {
        $data = $request->validate([
            'DocType'   => 'nullable|max:50',
            'FileName'  => 'nullable|max:255',
            'DMSPath'   => 'nullable|max:500',
        ]);
        $data['AssetID'] = $asset;
        AssetAttachment::create($data);
        return back()->with('success','Attachment saved (link/placeholder).');
    }

    public function destroy(int $asset, int $id)
    {
        AssetAttachment::where('AssetID',$asset)->where('Id',$id)->delete();
        return back()->with('success','Attachment deleted.');
    }
}
