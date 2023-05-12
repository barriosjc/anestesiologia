<div class="row">

  <div class="col-md-3">
    <div class="form-group  {{ $errors->has('name') ? 'has-error' : ''}}">
      <label for="name" class="control-label">{{ 'Nombre' }}</label>
      <input class="aMayusculas form-control" name="name" type="text" id="name" value="{{ isset($user->name) ? $user->name : ''}}" required>
      {!! $errors->first('name', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-5">
    <div class="form-group  {{ $errors->has('last_name') ? 'has-error' : ''}}">
      <label for="last_name" class="control-label">{{ 'Nombre Completo' }}</label>
      <input class="aMayusculas form-control" name="last_name" type="text" id="last_name" value="{{ isset($user->last_name) ? $user->last_name : ''}}" required>
      {!! $errors->first('last_name', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-3">
    <div class="form-group  {{ $errors->has('password') ? 'has-error' : ''}}">
      <label for="password" class="control-label">{{ 'Password' }}</label>
      <input class="form-control" name="password" type="password" id="password" required>
      {!! $errors->first('password', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group  {{ $errors->has('foto') ? 'has-error' : ''}}">
      <label for="foto" class="control-label">{{ 'foto' }}</label>
      <input class="form-control" name="foto" type="file" id="foto" value="{{ isset($user->foto) ? $user->foto : ''}}">
      {!! $errors->first('foto', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-2">
    <div class="form-group  {{ $errors->has('email_verified_at') ? 'has-error' : ''}}">
      <label for="email_verified_at" class="control-label">{{ 'Usu Verificado' }}</label>
      <input class="form-control" name="email_verified_at" type="datetime-local" id="email_verified_at" value="{{ isset($user->email_verified_at) ? $user->email_verified_at : ''}}">
      {!! $errors->first('email_verified_at', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-4">
    <div class="form-group  {{ $errors->has('email') ? 'has-error' : ''}}">
      <label for="email" class="control-label">{{ 'eMail' }}</label>
      <input class="form-control" name="email" type="text" id="email" value="{{ isset($user->email) ? $user->email : ''}}" required>
      {!! $errors->first('email', '<p class="help-block">:message</p>') !!}
    </div>
  </div>

  <div class="col-md-12">
    <div class="form-group">
      <button class="btn btn-primary" type="submit" value=""><?php echo ($formMode === 'edit' ? 'Modificar' : 'Crear'); ?></button>
    </div>
  </div>
</div>

<script>
  $(function() {
      $(".aMayusculas").on("keyup", function() {
        this.value = this.value.toUpperCase();
      });
    });
</script>