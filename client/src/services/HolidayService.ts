import { ApiService } from "@/services";

export default class HolidayService extends ApiService {
  /**
   * Sends a request for month to the API and handles the response.
   *
   * Returns a `Promise<String>`.
   * @param year the year to get the holidays for
   */
  static getHolidays(year: string) {
    const url = this.getBaseUrl() + "holiday/" + year;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
