agendaCtrl = ($state, User, Center) ->
	vm = @	

	# ---- Choose an option from select option #
	typeChoseed = () ->
		User.UserCanByType vm.selectedType
			.then (data) ->
				if data.success is "true"
					Center.SelectCabina(vm.selectedType)
					redirectByType()
				else 
					alert data.message
	
	# ---- Redirect after selecting an available service #
	# TODO: Figure out a way to have validations not hardcoded #
	redirectByType = () ->
		goTo = "main"
		if vm.selectedType is "24"
			goTo = "private"
		else if vm.selectedType is "26"
			goTo = 'semi'
		else if vm.selectedType is "25"
			goTo = 'grupal'
		$state.go(goTo)
		return

	# ---- #
	getCabinas = () ->
		Center.GetCabinas(1)
			.then (data) ->
				vm.Cabinas = data;
				return
		return

	# ---- Initial loading function #
	init = () ->
		if User.IsLogged is false
			alert 'No ha iniciado sesion'
			return false
		do getCabinas
		return

	# ----  Declarations #
	vm.init = init
	vm.selectedType = ''

	# ---- Functions #
	vm.typeChoseed = typeChoseed
		
	do vm.init
	return

angular
	.module('appAgenda')
	.controller('agendaCtrl', agendaCtrl)

agendaCtrl
	.$inject = ['$state', 'UserData', 'CenterDataService']