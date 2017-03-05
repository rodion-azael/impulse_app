agendaPrivateCtrl = (UserData, PrivateService, Center) ->
	vm = @	

	# ---- Change Calendar pick plugin flag to open/close #
	openCalendar = () ->
		vm.calendarOpened = true
		return

	# ---- Change the date #
	changeDate	= () ->	
		vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY')
		do getCabinaDetails
		return

	# ---- An empty slot clicked. Creating new private sesion #
	newPrivateSesion = (timespan) ->
		if confirm '¿Desea agendar una sesión para el dia ' + timespan.format('DD MMM YYYY') + ' a las ' + timespan.format('hh:mm') + '?'
			obj = {
	      start: timespan.format('YYYY-MM-DD HH:mm:ss')
	      end: timespan.add(30, 'minutes').format('YYYY-MM-DD HH:mm:ss')
	      DURACION_CITA: 30,
	      user: UserData.User.client_id
	      type: '24'
			}
			PrivateService.AddSesion(obj)
				.then (data) ->
					do getCabinaDetails
					return
		return

	# ---- Converts service response to Calendar obj #
	dataToCalendar = () ->
		vm.events = []
		for app in vm.Cabina.Appnmts
			vm.events.push {
				id: app.id
				title: app.title,
				user_id: app.user_id
				startsAt: new Date(app.start)
				endsAt: new Date(app.end)
				draggable: false
			}
		vm.doneLoading = true
		return

	# ---- Load all cabina details #
	getCabinaDetails = () ->
		cabinaID = do Center.SelectedCabina
		date = moment(vm.appDate).format('YYYY/MM/DD')
		Center.GetCabinaDetails(cabinaID, date)
			.then (data) ->
				vm.Cabina = data.data
				do dataToCalendar
				return
		return
	
	# ---- Initial loading function #
	init = () ->
		if UserData.IsLogged is false
			alert 'No ha iniciado sesion'
			return false
		do getCabinaDetails
		return

	# ---- Declarations #
	# ---- Pass to View Model (vm) only those funcitons or variables needed on the html #
	vm.init = init
	vm.calendarOpened = false
	vm.appDate = new Date()
	vm.openCalendar = openCalendar
	vm.readAbleDate = moment(vm.appDate).format('DD MMM YYYY')
	vm.changeDate = changeDate
	vm.newPrivateSesion = newPrivateSesion
	vm.Cabina = {}
	vm.events = []
	vm.doneLoading = false

	# ---- Calendar vars #
	vm.calendarView = "day"
	vm.Apertura = '08:00:00'
	vm.Cierre = '19:00:00'
		
	do vm.init
	return

angular
	.module('appAgenda')
	.controller('agendaPrivateCtrl', agendaPrivateCtrl)

agendaPrivateCtrl
	.$inject = ['UserData', 'PrivateService', 'CenterDataService']