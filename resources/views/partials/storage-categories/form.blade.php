<div class="form-group">
    <label for="des">Storage Types</label>
    <select class="form-control" name="StorageTypes" id="StorageTypes">
        @foreach($storageTypes as $type)
            <option value='{{$type->id}}'>{{$type->type_name}}</option>
        @endforeach
    </select>
</div>
<div class="form-group">
    <label for="title">Meter Name</label>
    <input type="hidden" class="form-control" name="MeterName" id="MeterName">
    <input type="hidden" class="form-control" name="MeterRates" id="MeterRates">
    <input class="form-control" type="text" id="Suggest_MeterName" placeholder="Type to search" autocomplete="off" >
</div>
<div class="form-group">
    <label for="title">Sub Category</label>
    <input type="hidden" class="form-control" name="MeterSubCategory" id="MeterSubCategory">
    <input class="form-control" type="text" id="Suggest_SubCategory" placeholder="Click to select" autocomplete="off" >
</div>
<div class="form-group">
    <label for="des">Unit</label>
    <input type="text" class="form-control" name="Unit" id="Unit" readonly>
</div>
<div class="form-group">
    <label for="des">RAM</label>
    <input type="text" class="form-control" name="RAM" id="RAM">
</div>
<div class="form-group">
    <label for="des">Cost</label>
    <input type="text" class="form-control" name="Cost" id="Cost" readonly>
</div>