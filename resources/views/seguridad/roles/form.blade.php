<div class="row">

  <div class="col-md-3">
    <div class="form-group  {{ $errors->has('name') ? 'has-error' : ''}}">
      <label for="name" class="control-label">{{ 'Nombre' }}</label>
      <input class="form-control" name="name" type="text" id="name" value="{{ isset($roles->name) ? $roles->name : ''}}" required>
      {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-5">
    <div class="form-group  {{ $errors->has('guard_name') ? 'has-error' : ''}}">
      <label for="guard_name" class="control-label">{{ 'Guard name' }}</label>
      <input class="form-control" name="guard_name" type="text" id="guard_name" value="{{ isset($roles->guard_name) ? $roles->guard_name : ''}}" required>
      {!! $errors->first('guard_name', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <button class="btn btn-primary" type="submit" value=""><?php echo ($formMode === 'edit' ? 'Modificar' : 'Crear'); ?></button>
    </div>
  </div>
</div>