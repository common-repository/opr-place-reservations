function documentReady() {
   fetchAndShowAvailableFilteredPlaces();

   const filterPlacesListButton = document.querySelector('#placesFilter-action-filter');
   if (filterPlacesListButton)
      filterPlacesListButton.addEventListener('click', fetchAndShowAvailableFilteredPlaces);

   const addReservationButton = document.querySelector('#addNewReservation-action-add');
   if (addReservationButton)
      addReservationButton.addEventListener('click', addReservation);

   const placeInput = document.querySelector('#addNewReservation-placeId');
   if (placeInput)
      placeInput.addEventListener('change', updatePeriodTypeValueByPlace);
   
}
document.addEventListener('DOMContentLoaded', documentReady, false);