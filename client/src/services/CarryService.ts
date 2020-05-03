import ApiService from "@/services/ApiService";
import { Carry } from "@/models";

export default class CarryService extends ApiService {
  static getCarryResultByMonth(params: string) {
    const url = this.getBaseUrl() + "carry-result/" + params;
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static getCarry() {
    const url = this.getBaseUrl() + "carry";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "GET",
      headers: headers,
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }

  static setCarry(carry: Carry) {
    const url = this.getBaseUrl() + "carry";
    let headers = this.getHeaders();
    headers = { ...headers, ...this.getAuthHeader() };
    const requestOptions = {
      method: "POST",
      headers: headers,
      body: JSON.stringify(carry),
    };
    return fetch(url, requestOptions).then(this.handleResponse);
  }
}
