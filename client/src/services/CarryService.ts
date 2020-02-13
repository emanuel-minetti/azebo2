import ApiService from "@/services/ApiService";

export default class CarryService extends ApiService {
  static getByMonth(year: string, month: string) {
    const url = this.getBaseUrl() + "carry-result/" + year + "/" + month;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
