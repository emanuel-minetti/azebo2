import ApiService from "@/services/ApiService";

export default class CarryService extends ApiService {
  static getCarryByMonth(params: string) {
    const url = this.getBaseUrl() + "carry-result/" + params;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
